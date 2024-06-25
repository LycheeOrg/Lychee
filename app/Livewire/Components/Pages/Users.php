<?php

namespace App\Livewire\Components\Pages;

use App\Actions\User\Create;
use App\Exceptions\UnauthorizedException;
use App\Livewire\Components\Menus\LeftMenu;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * User management page.
 * Here we create, delete, modify users.
 */
class Users extends Component
{
	/** @var Collection<int,User> */
	public Collection $users;
	private Create $create;

	public string $username = '';
	public string $password = '';
	public bool $may_upload = false;
	public bool $may_edit_own_settings = false;

	/**
	 * Init the private properties.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->create = resolve(Create::class);
	}

	/**
	 * Load users.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		Gate::authorize(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, User::class);

		$this->loadUsers();
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.users');
	}

	/**
	 * Refresh the user List.
	 *
	 * @return void
	 */
	public function loadUsers(): void
	{
		$this->users = User::orderBy('id', 'asc')->get();
	}

	/**
	 * Create a new user.
	 *
	 * @return void
	 */
	public function create(): void
	{
		Gate::authorize(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, User::class);

		// Create user
		$this->create->do(
			username: $this->username,
			password: $this->password,
			mayUpload: $this->may_upload,
			mayEditOwnSettings: $this->may_edit_own_settings);

		// reset attributes and reload user list (triggers refresh)
		$this->username = '';
		$this->password = '';
		$this->may_upload = false;
		$this->may_edit_own_settings = false;
		$this->loadUsers();
	}

	public function back(): mixed
	{
		$this->dispatch('closeLeftMenu')->to(LeftMenu::class);

		return $this->redirect(route('livewire-gallery'), true);
	}

	/**
	 * Delete an existing credential.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function delete(int $id): void
	{
		if ($id === Auth::id()) {
			throw new UnauthorizedException('You are not allowed to delete yourself');
		}

		Gate::authorize(UserPolicy::CAN_CREATE_OR_EDIT_OR_DELETE, [User::class]);

		User::where('id', '=', $id)->delete();
		$this->loadUsers();
	}
}
