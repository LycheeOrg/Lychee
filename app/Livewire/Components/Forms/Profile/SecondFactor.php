<?php

namespace App\Livewire\Components\Forms\Profile;

use App\Exceptions\UnauthenticatedException;
use App\Livewire\Traits\Notify;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Attributes\On;
use Livewire\Component;

class SecondFactor extends Component
{
	use Notify;

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	#[On('reload-component')]
	public function render(): View
	{
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		return view('livewire.modules.profile.second-factor');
	}

	/**
	 * Return the list of credentials associated to the current logged in user.
	 *
	 * @return Collection<int,WebAuthnCredential>
	 *
	 * @throws UnauthenticatedException
	 */
	public function getCredentialsProperty(): Collection
	{
		/** @var \App\Models\User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		return $user->webAuthnCredentials;
	}

	/**
	 * Delete an existing credential.
	 *
	 * @param string $id
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function delete(string $id): void
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$user->webAuthnCredentials()->where('id', '=', $id)->delete();
		$this->notify(__('lychee.U2F_CREDENTIALS_DELETED'));
	}
}
