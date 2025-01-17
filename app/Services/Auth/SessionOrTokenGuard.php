<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Services\Auth;

use App\Exceptions\BadRequestHeaderException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * A custom Guard which combines the default Laravel Session Guard with
 * a token Guard in an OR-like fashion.
 *
 * In Laravel terms a Guard is a service class which defines _how_ the user
 * of a request is determined
 * (see https://laravel.com/docs/9.x/authentication#introduction).
 * This guard behaves as follows:
 *
 *  _ If a request has neither a session nor sends an API token, the request
 *    counts as unauthenticated.
 *    In other words, the returned user equals `null`.
 *  - If a request has no session, but the request sends an API token, the
 *    API token is used for user authentication.
 *  - If a request has a session and sends no API token, the previously
 *    stored user is loaded from the session.
 *    If such a user does not exist, the request is unauthenticated.
 *  - If a request has both a session _and_ sends an API token, this guard
 *    ensures consistency between the both; i.e. the user stored inside the
 *    session and indicated by the token must be the same.
 *    Normally, a client is expected to either use session-based login or
 *    use token authentication, not both simultaneously.
 *    If a client is crazy enough to use both but fails to keep it consistent,
 *    this counts as a client-side error.
 *    Lychee also uses a session for other things like storing the list of
 *    unlocked albums and a "jumping" user might lead to leaking undesired
 *    information.
 *
 * Laravel ships with three guards out-of-the-box:
 *
 *  - {@link \Illuminate\Auth\SessionGuard}
 *  - {@link \Illuminate\Auth\TokenGuard}
 *  - {@link \Illuminate\Auth\RequestGuard}
 *
 * which behave in the obvious way:
 *
 *  - {@link \Illuminate\Auth\SessionGuard} is an stateful guard and works
 *    together with {@link \Illuminate\Contracts\Session\Session}.
 *    This guard expects that the user is once explicitly set via `loginAs`
 *    (or similar methods). Then the guard stores the user inside the session
 *    and fetches the previously stored user from the session during
 *    subsequent request.
 *  - {@link \Illuminate\Auth\TokenGuard} is a stateless guard and uses a
 *    proprietary HTTP header to transmit an "API token".
 *    The token must be sent by the client for each request.
 *  - {@link \Illuminate\Auth\RequestGuard} is a stateless guard and uses a
 *    standard HTTP authentication techniques (such as HTTP digest) via the
 *    HTTP authentication header.
 *
 * The available guards are configured in `config/auth.php`.
 * Note that the configuration file uses the term "guard" in a different way
 * than above (Laravel I love you soooo much).
 * In `config/auth.php` several "presets" can be configured below the array
 * key `'guards'`, the actual guard to be used (as listed above) is configured
 * via `'driver'`.
 * The default preset to be used for all request unless stated otherwise on a
 * route, is set in `default`.
 * Laravel is built around the idea that a particular route uses either one
 * of the guards above, i.e. _either_ uses `SessionGuard`, `TokenGuard` or
 * `RequestGuard`.
 *
 * Lychee is special in this case.
 * Lychee allows to use a session-based login _or_ a token authentication
 * for the same route.
 * Hence, we use a special guard.
 *
 * Laravel uses {@link \Illuminate\Auth\AuthManager::resolve()} to resolve
 * the guard at runtime.
 * The built-in guards {@link \Illuminate\Auth\SessionGuard} and
 * {@link \Illuminate\Auth\TokenGuard} are created via
 * the magic line
 * `$driverMethod = 'create'.ucfirst($config['driver']).'Driver';`
 * which is why the "special" identifiers `"session"` and `"token"` work
 * inside the file `config/auth.php`.
 * Custom identifiers can be registered `Auth::extend` and become a
 * "custom creator" inside {@link \Illuminate\Auth\AuthManager::resolve()}.
 */
class SessionOrTokenGuard extends SessionGuard
{
	public const HTTP_TOKEN_HEADER = 'Authorization';
	public const TOKEN_COLUMN_NAME = 'token';
	public const TOKEN_HASH_METHOD = 'SHA512';

	public const AUTH_STATE_UNAUTHENTICATED = 0;
	public const AUTH_STATE_STATELESS = 1;
	public const AUTH_STATE_STATEFUL = 2;

	protected int $authState = self::AUTH_STATE_UNAUTHENTICATED;

	/**
	 * Creates an instance of this guard.
	 *
	 * As this class inherits the Laravel Session Guard, this method is
	 * copy-&-paste from
	 * {@link \Illuminate\Auth\AuthManager::createSessionDriver()} which
	 * creates the Laravel Session guard.
	 *
	 * @param array<string,mixed> $config
	 *
	 * @throws BindingResolutionException
	 */
	public static function createGuard(Application $app, string $name, array $config): self
	{
		$userProvider = Auth::createUserProvider($config['provider']);
		$guard = new self($name, $userProvider, $app->make('session.store'));
		$guard->setCookieJar($app->make('cookie'));
		$guard->setDispatcher($app->make('events'));
		/** @disregard P1013 */
		$guard->setRequest($app->refresh('request', $guard, 'setRequest'));
		if (isset($config['remember'])) {
			// @codeCoverageIgnoreStart
			$guard->setRememberDuration($config['remember']);
			// @codeCoverageIgnoreEnd
		}

		return $guard;
	}

	/**
	 * Returns the user of the current request.
	 *
	 * This method is a merger of
	 * {@link SessionGuard::user} and {@link \Illuminate\Auth\TokenGuard::user}.
	 * This method is the main "working horse" and behaves as described in
	 * the class comment.
	 *
	 * @return ?Authenticatable
	 *
	 * @throws BadRequestHeaderException
	 * @throws \RuntimeException
	 */
	public function user(): Authenticatable|null
	{
		// If we've already retrieved the user for the current request we can just
		// return it back immediately. We do not want to fetch the user data on
		// every call to this method because that would be tremendously slow.
		if ($this->user !== null) {
			return $this->user;
		}

		// First, try to get a user by token.
		$userByToken = $this->getUserByToken();

		// Second, try to get a user by stored user ID on the session.
		$userIdBySession = $this->session->get($this->getName());
		$userBySession = $userIdBySession !== null ? $this->provider->retrieveById($userIdBySession) : null;

		// Third, if `$userBySession` is null, but we decrypt a "recaller"
		// cookie we attempt to pull the user data from that cookie which
		// serves as a remember-me cookie
		$recaller = $userBySession === null ? $this->recaller() : null;
		$userByRecaller = $recaller !== null ? $this->userFromRecaller($recaller) : null;

		// We step through the different combinations which may happen,
		// because we use a combination of token and session.
		if ($userBySession !== null) {
			if ($userByToken === null || $userBySession->getAuthIdentifier() === $userByToken->getAuthIdentifier()) {
				// We are good, no contradiction!
				// We call the parent method here to skip the additional token
				// check added by the overwritten method of this class.
				parent::setUser($userBySession);
				// `setUser()` sets `authState` to stateless, but here we
				// used the user from a previous session _without_ logging in
				// again, hence we must set `authState` explicitly.
				$this->authState = self::AUTH_STATE_STATEFUL;
			} else {
				throw new BadRequestHeaderException('Token- and session-based user mismatch');
			}
		} elseif ($userByToken !== null) {
			// A token-based authentication is considered stateless, so we
			// call `setUser` and not `login`.
			// We call the parent method here to skip the additional token
			// check added by the overwritten method of this class.
			parent::setUser($userByToken);
			// As we called the parent method `setUser`, we must set the
			// new authentication state explicitly.
			$this->authState = self::AUTH_STATE_STATELESS;
		} elseif ($userByRecaller !== null) {
			// @codeCoverageIgnoreStart
			$this->login($userByRecaller, true);
		// @codeCoverageIgnoreEnd
		} else {
			// In the other cases, `$this->user` has implicitly been set by
			// `parent::setUser` or `$this->login`.
			$this->authState = self::AUTH_STATE_UNAUTHENTICATED;
			$this->user = null;
		}

		return $this->user;
	}

	/**
	 * Get the ID for the currently authenticated user.
	 *
	 * This is a fixed variant of {@link \Illuminate\Auth\TokenGuard::id}
	 * which uses PHP 8 syntax and ensures that a value is always returned.
	 * We don't use the complicated variant of {@link SessionGuard::id},
	 * because {@link SessionOrTokenGuard::user()} ensures that
	 * {@link SessionOrTokenGuard::$loggedOut} and
	 * {@link SessionOrTokenGuard::$user} are always consistent.
	 *
	 * @return int|string|null
	 *
	 * @throws BadRequestHeaderException
	 * @throws \RuntimeException
	 */
	public function id(): int|string|null
	{
		return $this->user()?->getAuthIdentifier();
	}

	/**
	 * Sets the given user without changing the session.
	 *
	 * If an API token is given, setting another user than the user given by
	 * the API token is considered an error.
	 *
	 * If the method succeeds, {@link SessionOrTokenGuard::$authState} equals
	 * {@link SessionOrTokenGuard::AUTH_STATE_STATELESS} afterwards.
	 *
	 * @return $this
	 *
	 * @throws BadRequestHeaderException
	 */
	public function setUser(Authenticatable $user): static
	{
		$userByToken = $this->getUserByToken();
		if ($userByToken !== null && $user->getAuthIdentifier() !== $userByToken->getAuthIdentifier()) {
			throw new BadRequestHeaderException('Cannot set another user than the one provided by the API token');
		}
		parent::setUser($user);
		$this->authState = self::AUTH_STATE_STATELESS;

		return $this;
	}

	/**
	 * Logs-in the given user stateful.
	 *
	 * If an API token is given, logging in another user than the user
	 * given by the API token is considered an error.
	 *
	 * If the method succeeds, {@link SessionOrTokenGuard::$authState} equals
	 * {@link SessionOrTokenGuard::AUTH_STATE_STATEFUL} afterwards.
	 *
	 * @param AuthenticatableContract $user
	 * @param bool                    $remember
	 *
	 * @return void
	 *
	 * @throws BadRequestHeaderException
	 * @throws \RuntimeException
	 */
	public function login(AuthenticatableContract $user, $remember = false): void
	{
		parent::login($user, $remember);
		$this->authState = self::AUTH_STATE_STATEFUL;
	}

	/**
	 * Logs out the current stateful user.
	 *
	 * If the method succeeds, {@link SessionOrTokenGuard::$authState} equals
	 * {@link SessionOrTokenGuard::AUTH_STATE_STATELESS} or
	 * {@link SessionOrTokenGuard::AUTH_STATE_UNAUTHENTICATED} afterwards,
	 * depending on whether a token is given in the request or not.
	 *
	 * @return void
	 *
	 * @throws BadRequestHeaderException
	 * @throws \RuntimeException
	 */
	public function logout(): void
	{
		parent::logout();
		$this->authState = self::AUTH_STATE_UNAUTHENTICATED;

		// Re-authenticate as token-based user if given.
		$userByToken = $this->getUserByToken();
		if ($userByToken !== null) {
			// A token-based authentication is considered stateless, so we
			// call `setUser` and not `login`.
			// We call the parent method here to skip the additional token
			// check added by the overwritten method of this class.
			parent::setUser($userByToken);
			// As we called the parent method `setUser`, we must set the
			// new authentication state explicitly.
			$this->authState = self::AUTH_STATE_STATELESS;
		}
	}

	/**
	 * Returns the user denoted by the token in the HTTP header.
	 *
	 * @return ?Authenticatable The user denoted by the HTTP header or `null`
	 *                          if HTTP header is not set
	 *
	 * @throws BadRequestHeaderException thrown if the HTTP header with a
	 *                                   token is set, but no corresponding
	 *                                   user can be found
	 */
	protected function getUserByToken(): ?Authenticatable
	{
		$token = $this->request->headers->get(self::HTTP_TOKEN_HEADER);

		// Skip if token is not found.
		if ($token === null || !is_string($token) || $token === '') {
			return null;
		}

		// Skip if token starts with Basic: it is not related to Lychee.
		if (Str::startsWith('Basic', $token)) {
			// @codeCoverageIgnoreStart
			return null;
			// @codeCoverageIgnoreEnd
		}

		// Check if token starts with Bearer
		$hasBearer = Str::startsWith('Bearer', $token);
		/** @var bool $configLog */
		$configLog = config('auth.token_guard.log_warn_no_scheme_bearer');
		/** @var bool $configThrow */
		$configThrow = config('auth.token_guard.fail_bearer_authenticable_not_found', true);

		// If Token does not start with Bearer
		if (!$hasBearer && $configLog) {
			Log::warning('Auth token found, but Bearer prefix not provided.');
		}

		// Remove prefix and fetch authenticable.
		$token = trim(Str::remove('Bearer', $token));
		$authenticable = $this->provider->retrieveByCredentials([
			self::TOKEN_COLUMN_NAME => hash(self::TOKEN_HASH_METHOD, $token),
		]);

		return match (true) {
			$authenticable !== null => $authenticable,
			// @codeCoverageIgnoreStart
			$hasBearer && $configThrow => throw new BadRequestHeaderException('Invalid token'),
			$hasBearer => null,
			// @codeCoverageIgnoreEnd
			default => throw new BadRequestHeaderException('Invalid token'),
		};
	}
}
