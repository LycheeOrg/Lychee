<?php

namespace App\Http\Controllers;

use App\Actions\User\Create;
use App\Enum\OauthProvidersType;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Models\Configs;
use App\Models\OauthCredential;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Contracts\User as ContractsUser;
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
		/** @var ContractsUser */
		$user = $this->getUserFromOauth($provider);

		$credential = $this->fetchAssociatedUserFromDB($provider, $user->getId());

		if ($credential !== null) {
			Auth::login($credential->user);

			return redirect(route('livewire-gallery'));
		}

		if (!Configs::getValueAsBool('oauth_create_user_on_first_attempt')) {
			throw new UnauthorizedException('User not found!');
		}

		if (User::query()->where('username', '=', $user->getName() ?? $user->getEmail() ?? $user->getId())
			->when(
				$user->getEmail() !== null && $user->getEmail() !== '',
				fn ($q) => $q->orWhere('email', '=', $user->getEmail())
			)->exists()) {
			throw new UnauthorizedException('User already exists!');
		}

		$create = resolve(Create::class);
		$new_user = $create->do(
			username: $user->getName() ?? $user->getEmail() ?? $user->getId(),
			email: $user->getEmail(),
			password: strtr(base64_encode(random_bytes(8)), '+/', '-_'),
			mayUpload: Configs::getValueAsBool('oauth_grant_new_user_upload_rights'),
			mayEditOwnSettings: Configs::getValueAsBool('oauth_grant_new_user_modification_rights'));

		Auth::login($new_user);

		$this->saveOauth(
			provider: $provider,
			authedUser_id: $new_user->id,
			oauth_id: $user->getId());

		return redirect(route('livewire-gallery'));
	}

	/**
	 * Get the user from the driver.
	 *
	 * @param OauthProvidersType $provider
	 *
	 * @return ContractsUser
	 */
	private function getUserFromOauth(OauthProvidersType $provider): ContractsUser
	{
		return Socialite::driver($provider->value)->user();
	}

	/**
	 * Fetch the Oauth credential and user associated.
	 *
	 * @param OauthProvidersType $provider Oauth provider
	 * @param string             $user_id  to fetch with
	 *
	 * @return OauthCredential|null credential if found
	 */
	private function fetchAssociatedUserFromDB(OauthProvidersType $provider, string $user_id): OauthCredential|null
	{
		return OauthCredential::query()
			->with(['user'])
			->where('token_id', '=', $user_id)
			->where('provider', '=', $provider)
			->first();
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

		$this->saveOauth(
			provider: $provider,
			authedUser_id: $authedUser->id,
			oauth_id: $user->getId());

		return redirect(route('profile'));
	}

	/**
	 * Save a credential for a user.
	 *
	 * @param OauthProvidersType $provider      of credential
	 * @param int                $authedUser_id user ID already existing in the database
	 * @param string             $oauth_id      oauth id on the Oauth server side
	 *
	 * @return void
	 */
	private function saveOauth(OauthProvidersType $provider, int $authedUser_id, string $oauth_id): void
	{
		$credential = OauthCredential::create([
			'provider' => $provider,
			'user_id' => $authedUser_id,
			'token_id' => $oauth_id,
		]);
		$credential->save();
	}
}