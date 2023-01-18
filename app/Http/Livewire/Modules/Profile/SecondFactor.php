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

	protected $listeners = ['loadCredentials'];

	/**
	 * Load users.
	 *
	 * @return void
	 */
	public function mount()
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

	public function loadCredentials()
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$this->credentials = $user->webAuthnCredentials;
	}

	/**
	 * Create a new user.
	 *
	 * @param Create $create
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function create()
	{
		dd('DIE FOR NOW');
		// reset attributes and reload user list (triggers refresh)
		$this->loadCredentials();
	}
}
