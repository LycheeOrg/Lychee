<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Oauth\Oauth as OauthAction;
use App\Enum\CacheTag;
use App\Enum\OauthProvidersType;
use App\Events\TaggedRouteCacheUpdated;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Profile\ClearOauthRequest;
use App\Http\Requests\Profile\OauthListRequest;
use App\Http\Resources\Oauth\OauthRegistrationData;
use App\Models\OauthCredential;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpFoundationRedirectResponse;

class OauthController extends Controller
{
	public function __construct(
		private OauthAction $oauth,
	) {
	}

	/**
	 * Function callback from the Oauth server.
	 *
	 * @param string $provider
	 *
	 * @return Redirector|RedirectResponse
	 *
	 * @codeCoverageIgnore
	 */
	public function redirected(string $provider)
	{
		$provider_enum = $this->oauth->validateProviderOrDie($provider);

		// We are already logged in: Registration operation
		if (Auth::check()) {
			$this->oauth->registerOrDie($provider_enum);

			return redirect(route('profile'));
		}

		// Authentication operation
		$this->oauth->authenticateOrDie($provider_enum);

		return redirect(route('gallery'));
	}

	/**
	 * Function called to authenticate a user to an Oauth server.
	 *
	 * @param string $provider
	 *
	 * @return HttpFoundationRedirectResponse
	 *
	 * @codeCoverageIgnore
	 */
	public function authenticate(string $provider)
	{
		if (Auth::check()) {
			throw new UnauthorizedException('User already authenticated.');
		}

		$provider_enum = $this->oauth->validateProviderOrDie($provider);

		return Socialite::driver($provider_enum->value)->redirect();
	}

	/**
	 * Add some security on registration.
	 *
	 * @param string $provider
	 *
	 * @return HttpFoundationRedirectResponse
	 *
	 * @codeCoverageIgnore
	 */
	public function register(string $provider)
	{
		Auth::user() ?? throw new UnauthenticatedException();
		if (!Request::hasValidSignature(false)) {
			throw new UnauthorizedException('Registration attempted but not initialized.');
		}

		$provider_enum = $this->oauth->validateProviderOrDie($provider);
		Session::put($provider_enum->value, OauthAction::OAUTH_REGISTER);

		TaggedRouteCacheUpdated::dispatch(CacheTag::USER);

		return Socialite::driver($provider_enum->value)->redirect();
	}

	/**
	 * Delete the Oauth registration for a user.
	 *
	 * @param ClearOauthRequest $request
	 *
	 * @return void
	 */
	public function clear(ClearOauthRequest $request): void
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$user->oauthCredentials()->where('provider', '=', $request->provider())->delete();

		TaggedRouteCacheUpdated::dispatch(CacheTag::USER);
	}

	/**
	 * List available end points and registrations URLS.
	 *
	 * @return OauthRegistrationData[]
	 */
	public function listForUser(OauthListRequest $request): array
	{
		$oauth_data = [];

		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$credentials = $user->oauthCredentials()->get();

		foreach (OauthProvidersType::cases() as $provider) {
			$client_id = config('services.' . $provider->value . '.client_id');
			if ($client_id === null || $client_id === '') {
				continue;
			}

			// We create a signed route for 5 minutes
			$route = URL::signedRoute(
				name: 'oauth-register',
				parameters: ['provider' => $provider->value],
				expiration: now()->addMinutes(5),
				absolute: false);

			$oauth_data[] = new OauthRegistrationData(
				provider_type: $provider,
				is_enabled: $credentials->search(fn (OauthCredential $c) => $c->provider === $provider) !== false,
				registration_route: $route,
			);
		}

		return $oauth_data;
	}

	/**
	 * List available end points.
	 *
	 * @return OauthProvidersType[]
	 */
	public function listProviders(): array
	{
		$oauth_available = [];

		foreach (OauthProvidersType::cases() as $oauth_provider) {
			$client_id = config('services.' . $oauth_provider->value . '.client_id');
			if ($client_id === null || $client_id === '') {
				continue;
			}

			$oauth_available[] = $oauth_provider;
		}

		return $oauth_available;
	}
}