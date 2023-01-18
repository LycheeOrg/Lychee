<?php

namespace App\Http\Livewire\Modules\Users;

use App\Actions\User\Save;
use App\Exceptions\UnauthorizedException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserLine extends Component
{
	public User $user;
	// We cannot model bind hidden attributes, as a result, it is better to add properties to the component
	public string $username;
	public string $password = '';
	public bool $may_upload;
	public bool $may_edit_own_settings;

	public function mount(User $user)
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
	public function render()
	{
		return view('livewire.modules.users.user-line');
	}

	public function getHasChangedProperty()
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
	 *
	 * @throws ModelDBException
	 * @throws UnauthenticatedException
	 * @throws InvalidFormatException
	 */
	public function delete()
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
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
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
