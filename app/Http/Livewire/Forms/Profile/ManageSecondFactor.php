<?php

namespace App\Http\Livewire\Forms\Profile;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Component;

class ManageSecondFactor extends Component
{
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
		return view('livewire.forms.profile.form-manage-second-factor');
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
		$this->emitTo('modules.profile.second-factor', 'loadCredentials');
	}
}
