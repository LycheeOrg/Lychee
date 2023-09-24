<?php

namespace App\Livewire\Components\Forms\Profile;

use App\Exceptions\UnauthenticatedException;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SetEmail extends Component
{
	#[Locked] public string $description;
	#[Locked] public string $placeholder = 'email@example.com';
	#[Locked] public string $action;
	public ?string $value; // ! Wired

	/**
	 * Mount the description and action localization.
	 * We keep the value in the rendering to make it more dynamic.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		/** @var User $user */
		$user = Auth::user();
		$this->value = $user->email;
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
		return view('livewire.forms.settings.input');
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
		$this->validate(['value' => 'required|email']);

		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();
		$user->email = $this->value;
		$user->save();
	}
}