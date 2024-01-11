<?php

namespace App\Http\Controllers;

use App\Enum\OauthProvidersType;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpFoundationRedirectResponse;

class Oauth extends Controller
{
	public const OAUTH_REGISTER = 'register';
	public const OAUTH_INITIALIZE = 'initizalize';

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
		if (Session::get($providerEnum->value) !== self::OAUTH_INITIALIZE) {
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
		$candidateUser = User::query()
			->where($provider->value . '_id', '=', $user->getId())
			->first();

		if ($candidateUser === null) {
			throw new UnauthorizedException('User not found!');
		}

		Auth::login($candidateUser);

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

		/** @phpstan-ignore-next-line we are sure this exists because we force it with the enum */
		$authedUser->{$provider->value . '_id'} = $user->getId();
		$authedUser->save();
		Auth::login($authedUser);

		return redirect(route('profile'));
	}
}