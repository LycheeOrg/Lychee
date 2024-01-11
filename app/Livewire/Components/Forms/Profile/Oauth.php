<?php

namespace App\Livewire\Components\Forms\Profile;

use App\Enum\OauthProvidersType;
use App\Exceptions\UnauthenticatedException;
use App\Livewire\DTO\OauthData;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

/**
 * Retrieve the API token for the current user.
 * This is the Modal integration.
 */
class Oauth extends Component
{
	/**
	 * Renders the modal content.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		return view('livewire.forms.profile.oauth');
	}

	public function clear(string $provider): void
	{
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);
		$providerEnum = OauthProvidersType::from($provider);

		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();
		/** @phpstan-ignore-next-line we are sure this exists because we force it with the enum */
		$user->{$providerEnum->value . '_id'} = null;
		$user->save();

		Auth::login($user);
	}

	/**
	 * Return computed property for OauthData.
	 *
	 * @return array<int,OauthData>
	 */
	public function getOauthDataProperty(): array
	{
		$oauthData = [];

		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

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

			$oauthData[] = new OauthData(
				providerType: $provider->value,
				/** @phpstan-ignore-next-line we are sure this exists because we force it with the enum */
				isEnabled: $user->{$provider->value . '_id'} !== null,
				registrationRoute: $route,
			);
		}

		return $oauthData;
	}
}