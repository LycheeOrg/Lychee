<?php

namespace App\Http\Controllers;

use App\Enum\OauthProvidersType;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Models\OauthCredential;
use App\Models\User;
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
	public const OAUTH_REGISTER = 'register';

	/**
	 * Provide a valid provider Enum from string.
	 *
	 * @param string $provider
	 *
	 * @return OauthProvidersType
	 *
	 * @throws LycheeInvalidArgumentException
	 */
	private function validateProviderOrDie(string $provider): OauthProvidersType
	{
		$providerEnum = OauthProvidersType::tryFrom($provider);
		if ($providerEnum === null) {
			throw new LycheeInvalidArgumentException('unkown Oauth provider type');
		}

		return $providerEnum;
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
		$providerEnum = $this->validateProviderOrDie($provider);

		// We are already logged in: Registration operation
		if (Auth::check()) {
			return $this->registerOrDie($providerEnum);
		}

		// Authentication operation
		return $this->authenticateOrDie($providerEnum);
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

		$providerEnum = $this->validateProviderOrDie($provider);

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
		$providerEnum = $this->validateProviderOrDie($provider);

		Auth::user() ?? throw new UnauthenticatedException();
		if (!Request::hasValidSignature(false)) {
			throw new UnauthorizedException('Registration attempted but not initialized.');
		}

		Session::put($providerEnum->value, self::OAUTH_REGISTER);

		return Socialite::driver($providerEnum->value)->redirect();
	}

	/**
	 * Authenticate and redirect.
	 *
	 * @param OauthProvidersType $provider
	 *
	 * @return RedirectResponse
	 */
	private function authenticateOrDie(OauthProvidersType $provider)
	{
		$user = Socialite::driver($provider->value)->user();

		$credential = OauthCredential::query()
			->with(['user'])
			->where('token_id', '=', $user->getId())
			->where('provider', '=', $provider)
			->first();

		if ($credential === null) {
			throw new UnauthorizedException('User not found!');
		}

		Auth::login($credential->user);

		return redirect(route('livewire-gallery'));
	}

	/**
	 * Authenticate and redirect.
	 *
	 * @param OauthProvidersType $provider
	 *
	 * @return RedirectResponse
	 */
	private function registerOrDie(OauthProvidersType $provider)
	{
		if (Session::get($provider->value) !== self::OAUTH_REGISTER) {
			throw new UnauthorizedException('Registration attempted but not authorized.');
		}

		$user = Socialite::driver($provider->value)->user();

		/** @var User $authedUser */
		$authedUser = Auth::user();

		$count_existing = OauthCredential::query()
			->where('provider', '=', $provider)
			->where('user_id', '=', $authedUser->id)
			->count();
		if ($count_existing > 0) {
			throw new LycheeLogicException('Oauth credential for that provider already exists.');
		}

		$credential = OauthCredential::create([
			'provider' => $provider,
			'user_id' => $authedUser->id,
			'token_id' => $user->getId(),
		]);
		$credential->save();

		return redirect(route('profile'));
	}
}