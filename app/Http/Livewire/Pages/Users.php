<?php

namespace App\Http\Livewire\Pages;

use App\Actions\User\Create;
use App\Enum\Livewire\PageMode;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Users extends Component
{
	public PageMode $mode = PageMode::USERS;

	public Collection $users;

	public string $username = '';
	public string $password = '';
	public bool $may_upload = false;
	public bool $may_edit_own_settings = false;

	protected $listeners = ['loadUsers'];

	/**
	 * Load users.
	 *
	 * @return void
	 */
	public function mount()
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

	public function loadUsers()
	{
		$this->users = User::orderBy('id', 'asc')->get();
	}

	/**
	 * Create a new user.
	 *
	 * @param Create $create
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function create(Create $create)
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
