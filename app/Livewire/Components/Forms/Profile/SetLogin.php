<?php

namespace App\Livewire\Components\Forms\Profile;

use App\Legacy\Actions\Settings\UpdateLogin;
use App\Legacy\V1\RuleSets\ChangeLoginRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Livewire\Traits\Notify;
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
	use Notify;

	public string $oldPassword = ''; // ! wired
	public string $username = ''; // ! wired
	public string $password = ''; // ! wired
	public string $password_confirmation = ''; // ! wired

	private UpdateLogin $updateLogin;

	public function boot(): void
	{
		$this->updateLogin = resolve(UpdateLogin::class);
	}

	public function mount(): void
	{
		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.profile.set-login');
	}

	/**
	 * Update Username & Password of current user.
	 */
	public function submit(): void
	{
		$this->validate(ChangeLoginRuleSet::rules());
		$this->validate(['oldPassword' => new CurrentPasswordRule()]);

		$this->authorize(UserPolicy::CAN_EDIT, [User::class]);

		$currentUser = $this->updateLogin->do(
			$this->username,
			$this->password,
			$this->oldPassword,
			request()->ip()
		);

		// Update the session with the new credentials of the user.
		// Otherwise, the session is out-of-sync and falsely assumes the user
		// to be unauthenticated upon the next request.
		Auth::login($currentUser);
		$this->notify(__('lychee.CHANGE_SUCCESS'));

		$this->oldPassword = '';
		$this->username = '';
		$this->password = '';
		$this->password_confirmation = '';
	}

	/**
	 * Open a login modal box.
	 *
	 * @return void
	 */
	public function openApiTokenModal(): void
	{
		$this->openClosableModal('forms.profile.get-api-token', __('lychee.CLOSE'));
	}
}