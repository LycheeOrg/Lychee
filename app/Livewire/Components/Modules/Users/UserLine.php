<?php

namespace App\Livewire\Components\Modules\Users;

use App\Actions\User\Save;
use App\Http\RuleSets\Users\SetUserSettingsRuleSet;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * In the User management page, this represent a user (line).
 */
class UserLine extends Component
{
	use UseValidator;
	use Notify;

	private Save $save;
	public User $user;
	// We cannot model bind hidden attributes, as a result, it is better to add properties to the component
	#[Locked] public int $id;
	public string $username; // ! Wired
	public string $password = '';  // ! Wired
	public bool $may_upload; // ! Wired
	public bool $may_edit_own_settings; // ! Wired

	/**
	 * Initialization of private properties.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->save = resolve(Save::class);
	}

	/**
	 * Given a user, load the properties.
	 * Note that password stays empty to ensure that we do not update it by mistake.
	 *
	 * @param User $user
	 *
	 * @return void
	 */
	public function mount(User $user): void
	{
		Gate::authorize(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]);

		$this->user = $user;
		$this->id = $user->id;
		$this->username = $user->username;
		$this->may_edit_own_settings = $user->may_edit_own_settings;
		$this->may_upload = $user->may_upload;
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.modules.users.user-line');
	}

	/**
	 * computed property to check if the state is dirty.
	 * TODO: See if the dirty state of Livewire is usable instead.
	 *
	 * @return bool
	 */
	public function getHasChangedProperty(): bool
	{
		return $this->user->username !== $this->username ||
		$this->user->may_upload !== $this->may_upload ||
		$this->user->may_edit_own_settings !== $this->may_edit_own_settings ||
		$this->password !== '';
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @return void
	 */
	public function save(): void
	{
		if (!$this->areValid(SetUserSettingsRuleSet::rules())) {
			return;
		}

		Gate::authorize(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]);

		$this->save->do(
			$this->user,
			$this->username,
			$this->password,
			$this->may_upload,
			$this->may_edit_own_settings
		);
		$this->password = ''; // Reset
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}
