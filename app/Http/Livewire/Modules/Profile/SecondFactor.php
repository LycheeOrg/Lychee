<?php

namespace App\Http\Livewire\Modules\Profile;

use App\Actions\User\Create;
use App\Exceptions\UnauthenticatedException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class SecondFactor extends Component
{
	public Collection $credentials;

	/**
	 * Listeners for reloading the list of credentials.
	 * This allows to update the list after a new credential has been added or removed.
	 *
	 * @var string[]
	 */
	protected $listeners = ['loadCredentials'];

	/**
	 * Load users.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$this->loadCredentials();
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.profile.second-factor');
	}

	/**
	 * Fetch the credentials of the current user.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException
	 * @throws UnauthenticatedException
	 */
	public function loadCredentials(): void
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$this->credentials = $user->webAuthnCredentials;
	}

	/**
	 * Create a new U2F credential.
	 *
	 * @return void
	 */
	public function create(): void
	{
		// TODO: create token
		// reset attributes and reload user list (triggers refresh)
		$this->loadCredentials();
	}
}
