<?php

namespace App\Http\Livewire\Forms\Profile;

use App\Exceptions\UnauthenticatedException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SetEmail extends Component
{
	public string $description;
	public string $placeholder = 'email@example.com';
	public ?string $value; // ! Wired
	public string $action;

	/**
	 * Mount the description and action localization.
	 * We keep the value in the rendering to make it more dynamic.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$this->description = __('lychee.ENTER_EMAIL');
		$this->action = __('lychee.SAVE');
	}

	/**
	 * Render the component with the email form.
	 *
	 * @return View
	 *
	 * @throws UnauthenticatedException
	 */
	public function render(): View
	{
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$this->value = $user->email;

		return view('livewire.forms.form-input');
	}

	/**
	 * Save the email address entered.
	 *
	 * @return void
	 *
	 * @throws UnauthenticatedException
	 */
	public function save(): void
	{
		// TODO : VALIDATE
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$user->email = $this->value;
		$user->save();
	}
}