<?php

namespace App\Http\Livewire\Pages;

use App\Actions\User\Create;
use App\Enum\Livewire\PageMode;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * User management page.
 * Here we create, delete, modify users.
 */
class Users extends Component
{
	public PageMode $mode = PageMode::USERS;

	public Collection $users;

	public string $username = '';
	public string $password = '';
	public bool $may_upload = false;
	public bool $may_edit_own_settings = false;

	/**
	 * @var string[] listeners to refresh the page when creating a new user or deleting one
	 */
	protected $listeners = ['loadUsers'];

	/**
	 * Load users.
	 *
	 * @return void
	 */
	public function mount(): void
	{
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
	 * @param Create $create
	 *
	 * @return void
	 */
	public function create(Create $create): void
	{
		// Create user
		$create->do(
			$this->username,
			$this->password,
			$this->may_upload,
			$this->may_edit_own_settings);

		// reset attributes and reload user list (triggers refresh)
		$this->username = '';
		$this->password = '';
		$this->may_upload = false;
		$this->may_edit_own_settings = false;
		$this->loadUsers();
	}
}
