<?php

namespace App\Livewire\Components\Forms\Profile;

use App\Enum\OauthProvidersType;
use App\Exceptions\UnauthenticatedException;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Retrieve the API token for the current user.
 * This is the Modal integration.
 */
class Github extends Component
{
	#[Locked] public bool $isEnabled;
	#[Locked] public string $registerRoute;
	/**
	 * Renders the modal content.
	 *
	 * @return View
	 */
	public function render(): View
	{
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$this->isEnabled = $user->github_id !== null;
		$this->registerRoute = route('oauth-register', ['provider' => OauthProvidersType::GITHUB]);

		return view('livewire.forms.profile.github');
	}

	public function clear(): void
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$user->github_id = null;
		$user->save();

		Auth::login($user);
	}
}