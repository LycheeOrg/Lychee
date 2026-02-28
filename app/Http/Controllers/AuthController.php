<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\User\ProvisionLdapUser;
use App\DTO\LdapConfiguration;
use App\Exceptions\LdapConnectionException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\Session\LoginRequest;
use App\Http\Resources\Models\UserResource;
use App\Http\Resources\Rights\GlobalRightsResource;
use App\Http\Resources\Root\AuthConfig;
use App\Models\User;
use App\Services\Auth\LdapService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Controller responsible for the authentication of the user.
 */
class AuthController extends Controller
{
	/**
	 * Login tentative.
	 *
	 * @param LoginRequest $request
	 *
	 * @return void
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 */
	public function login(LoginRequest $request): void
	{
		$username = $request->username();
		$password = $request->password();
		$ip = $request->ip();
		$remember = $request->rememberMe();

		try {
			// Try LDAP authentication first if enabled
			if ($this->isLdapEnabled($request) && $this->attemptLdapLogin($username, $password, $remember)) {
				Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . ' -- User (' . $username . ') has logged in via LDAP from ' . $ip . ' [remember=' . ($remember ? 'true' : 'false') . ']');

				return;
			}
		} catch (LdapConnectionException $e) {
			// LDAP server unreachable - fall through to local auth if enabled
			Log::channel('login')->warning(__METHOD__ . ':' . __LINE__ . ' -- LDAP server unreachable, falling back to local auth for user (' . $username . ') from ' . $ip);
		}

		// Fallback to local authentication
		if (Auth::attempt([
			'username' => $username,
			'password' => $password,
		], $remember)) {
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . ' -- User (' . $username . ') has logged in from ' . $ip . ' [remember=' . ($remember ? 'true' : 'false') . ']');

			return;
		}

		Log::channel('login')->error(__METHOD__ . ':' . __LINE__ . ' -- User (' . $username . ') has tried to log in from ' . $ip);
		throw new UnauthenticatedException('Unknown user or invalid password');
	}

	/**
	 * Unsets the session values.
	 *
	 * @return void
	 */
	public function logout(): void
	{
		Auth::logout();
		Session::flush();
	}

	/**
	 * Get the global rights of the current user.
	 */
	public function getGlobalRights(): GlobalRightsResource
	{
		return new GlobalRightsResource();
	}

	/**
	 * First function being called via AJAX.
	 *
	 * @return UserResource
	 */
	public function getCurrentUser(): UserResource
	{
		/** @var User|null $user */
		$user = Auth::user();

		return new UserResource($user);
	}

	/**
	 * Return the configuration for the authentication.
	 *
	 * @return AuthConfig
	 */
	public function getConfig(): AuthConfig
	{
		return new AuthConfig();
	}

	/**
	 * Check if LDAP authentication is enabled.
	 *
	 * @return bool
	 */
	protected function isLdapEnabled(Request $request): bool
	{
		return $request->verify()->is_supporter() && config('ldap.auth.enabled', false) === true;
	}

	/**
	 * Attempt LDAP authentication and provision user.
	 *
	 * @param string $username LDAP username
	 * @param string $password User password
	 *
	 * @return bool True if authentication succeeded, false otherwise
	 */
	private function attemptLdapLogin(string $username, string $password, bool $remember = false): bool
	{
		try {
			// Create LDAP configuration and service
			$ldap_service = $this->getLdapService();

			// Authenticate against LDAP
			$ldap_user = $ldap_service->authenticate($username, $password);

			if ($ldap_user === null) {
				// Invalid credentials or user not found
				return false;
			}

			// Provision (create or update) local user
			$provision_action = new ProvisionLdapUser($ldap_service);
			$user = $provision_action->do($ldap_user);

			// Log the user in
			Auth::login($user, $remember);

			return true;
		} catch (LdapConnectionException $e) {
			// LDAP server unreachable - log with connection context
			Log::channel('login')->error(__METHOD__ . ':' . __LINE__ . ' -- LDAP server unreachable: ' . $e->getMessage(), [
				'username' => $username,
				'exception' => get_class($e),
			]);

			// Rethrow to allow graceful degradation to local auth
			throw $e;
		} catch (\Throwable $e) {
			// Other LDAP errors (configuration, provisioning, etc.) - log and return false
			Log::channel('login')->warning(__METHOD__ . ':' . __LINE__ . ' -- LDAP authentication failed: ' . $e->getMessage(), [
				'username' => $username,
				'exception' => get_class($e),
			]);

			return false;
		}
	}

	/**
	 * Return the LDAP service to allow for testing.
	 *
	 * @return LdapService
	 */
	protected function getLdapService(): LdapService
	{
		$ldap_config = new LdapConfiguration();

		return new LdapService($ldap_config);
	}
}
