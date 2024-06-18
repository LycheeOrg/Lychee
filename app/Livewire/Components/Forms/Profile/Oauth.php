<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Profile;

use App\Enum\OauthProvidersType;
use App\Exceptions\UnauthenticatedException;
use App\Livewire\DTO\OauthData;
use App\Models\OauthCredential;
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
		$user->oauthCredentials()->where('provider', '=', $providerEnum)->delete();
	}

	/**
	 * Return computed property for OauthData.
	 *
	 * @return array<string,OauthData>
	 */
	public function getOauthDataProperty(): array
	{
		$oauthData = [];

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

			$oauthData[$provider->value] = new OauthData(
				providerType: $provider->value,
				isEnabled: $credentials->search(fn (OauthCredential $c) => $c->provider === $provider) !== false,
				registrationRoute: $route,
			);
		}

		return $oauthData;
	}
}