<?php

namespace App\Http\Livewire\Modules\Users;

use App\Actions\User\Save;
use App\Exceptions\UnauthorizedException;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * In the User management page, this represent a user (line).
 */
class UserLine extends Component
{
	public User $user;
	// We cannot model bind hidden attributes, as a result, it is better to add properties to the component
	public string $username; // ! Wired
	public string $password = '';  // ! Wired
	public bool $may_upload; // ! Wired
	public bool $may_edit_own_settings; // ! Wired

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
		$this->user = $user;
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
	 * Deletes a user.
	 *
	 * The albums and photos owned by the user are re-assigned to the
	 * admin user.
	 *
	 * @return void
	 */
	public function delete(): void
	{
		if ($this->user->id === Auth::id()) {
			throw new UnauthorizedException('You are not allowed to delete yourself');
		}
		$this->user->delete();
		$this->emitTo('pages.users', 'loadUsers');
	}

	/**
	 * Save modification done to a user.
	 * Note that an admin can change the password of a user at will.
	 *
	 * @param Save $save
	 *
	 * @return void
	 */
	public function save(Save $save): void
	{
		$save->do(
			$this->user,
			$this->username,
			$this->password,
			$this->may_upload,
			$this->may_edit_own_settings
		);
	}
}
