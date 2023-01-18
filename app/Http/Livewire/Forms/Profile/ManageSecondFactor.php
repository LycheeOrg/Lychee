<?php

namespace App\Http\Livewire\Forms\Profile;

use Illuminate\Support\Str;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Livewire\Component;

class ManageSecondFactor extends Component
{
	public WebAuthnCredential $credential;

	public string $alias; // ! wired

	public function mount(WebAuthnCredential $credential)
	{
		$this->credential = $credential;
		$this->alias = $credential->alias ?? Str::substr($credential->id, 0, 30);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render()
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
	 *
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 * @throws \RuntimeException
	 */
	public function updated($field, $value)
	{
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
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 * @throws InvalidFormatException
	 */
	public function delete()
	{
		$this->credential->delete();
		$this->emitTo('modules.profile.second-factor', 'loadCredentials');
	}
}
