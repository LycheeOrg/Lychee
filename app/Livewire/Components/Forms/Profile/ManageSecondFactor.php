<?php

namespace App\Livewire\Components\Forms\Profile;

use App\Livewire\Components\Modules\Profile\SecondFactor;
use App\Livewire\Traits\Notify;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Component;

class ManageSecondFactor extends Component
{

	use Notify;
	
	/**
	 * Credential used.
	 *
	 * @var WebAuthnCredential
	 */
	public WebAuthnCredential $credential;

	/** @var string alias to rename the credentials. By default we provide the first parts of the ID */
	public string $alias; // ! wired

	/**
	 * Just mount the component with the required WebAuthn Credentials.
	 *
	 * @param WebAuthnCredential $credential
	 *
	 * @return void
	 */
	public function mount(WebAuthnCredential $credential): void
	{
		$this->credential = $credential;
		$this->alias = $credential->alias ?? Str::substr($credential->id, 0, 30);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.profile.manage-second-factor');
	}

	/**
	 * This runs after a wired property is updated.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function updated($field, $value): void
	{
		// TODO: ADD VALIDATION
		$this->credential->alias = $this->alias;
		$this->credential->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}

	/**
	 * Deletes a user.
	 *
	 * The albums and photos owned by the user are re-assigned to the
	 * admin user.
	 *
	 * @return void
	 */
	public function delete(): void
	{
		$this->credential->delete();
		$this->dispatch('loadCredentials')->to(SecondFactor::class);
	}
}
