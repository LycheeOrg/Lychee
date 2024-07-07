<?php

namespace App\Http\Controllers;

use App\Actions\Oauth\Oauth as OauthAction;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpFoundationRedirectResponse;

class Oauth extends Controller
{
	public function __construct(
		private OauthAction $oauth = new OauthAction(),
	) {
	}

	/**
	 * Function callback from the Oauth server.
	 *
	 * @param string $provider
	 *
	 * @return Redirector|RedirectResponse
	 */
	public function redirected(string $provider)
	{
		$providerEnum = $this->oauth->validateProviderOrDie($provider);

		// We are already logged in: Registration operation
		if (Auth::check()) {
			$this->oauth->registerOrDie($providerEnum);

			return redirect(route('profile'));
		}

		// Authentication operation
		$this->oauth->authenticateOrDie($providerEnum);

		return redirect(route('livewire-gallery'));
	}

	/**
	 * Function called to authenticate a user to an Oauth server.
	 *
	 * @param string $provider
	 *
	 * @return HttpFoundationRedirectResponse
	 */
	public function authenticate(string $provider)
	{
		if (Auth::check()) {
			throw new UnauthorizedException('User already authenticated.');
		}

		$providerEnum = $this->oauth->validateProviderOrDie($provider);

		return Socialite::driver($providerEnum->value)->redirect();
	}

	/**
	 * Add some security on registration.
	 *
	 * @param string $provider
	 *
	 * @return HttpFoundationRedirectResponse
	 */
	public function register(string $provider)
	{
		Auth::user() ?? throw new UnauthenticatedException();
		if (!Request::hasValidSignature(false)) {
			throw new UnauthorizedException('Registration attempted but not initialized.');
		}

		$providerEnum = $this->oauth->validateProviderOrDie($provider);
		Session::put($providerEnum->value, OauthAction::OAUTH_REGISTER);

		return Socialite::driver($providerEnum->value)->redirect();
	}
}