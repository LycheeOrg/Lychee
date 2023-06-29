<?php

namespace App\Http\Livewire\Forms\Profile;

use App\Actions\Settings\UpdateLogin;
use App\Http\Livewire\Traits\InteractWithModal;
use App\Http\RuleSets\ChangeLoginRuleSet;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\CurrentPasswordRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Because Livewire is sending the data to the client,
 * we do not provide the model as public property.
 */
class SetLogin extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;

	public string $oldPassword = ''; // ! wired
	public string $username = ''; // ! wired
	public string $password = ''; // ! wired
	public string $confirm = ''; // ! wired

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.profile.form-set-login');
	}

	/**
	 * Update Username & Password of current user.
	 */
	public function submit(UpdateLogin $updateLogin): void
	{
		/**
		 * For the validation to work it is important that the above wired property match
		 * the keys in the rules applied.
		 */
		$this->validate(ChangeLoginRuleSet::rules());
		$this->validate(['oldPassword' => new CurrentPasswordRule()]);

		/**
		 * Authorize the request.
		 */
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		$currentUser = $updateLogin->do(
			$this->username,
			$this->password,
			$this->oldPassword,
			request()->ip()
		);

		// Update the session with the new credentials of the user.
		// Otherwise, the session is out-of-sync and falsely assumes the user
		// to be unauthenticated upon the next request.
		Auth::login($currentUser);
	}

	/**
	 * Open a login modal box.
	 *
	 * @return void
	 */
	public function openApiTokenModal(): void
	{
		$this->openClosableModal('forms.profile.get-api-token', 'CANCEL');
	}
}