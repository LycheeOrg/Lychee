<?php

namespace App\Livewire\Components\Pages;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\AccessPermission;
use App\Models\BaseAlbumImpl;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Sharing page, allows the user to have a summary of all the sharing rights applied to their albums.
 * Allows to edit or delete.
 * Does not allow to add!
 */
class Sharing extends Component
{
	/**
	 * return the list of Permissions for current user.
	 *
	 * @return array<int,AccessPermission>
	 */
	public function getPermsProperty(): array
	{
		// This could be optimized, but whatever.
		return
			AccessPermission::with(['album', 'user'])
			->when(!Auth::user()->may_administrate, fn($q) => $q->whereIn('base_album_id', BaseAlbumImpl::select('id')->where('owner_id', '=', Auth::id())))
			->whereNotNull('user_id')
			->orderBy('base_album_id', 'asc')
			->get()->all();
	}

	/**
	 * Set up the profile page.
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function mount(): void
	{
		Gate::authorize(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, null]);
	}

	/**
	 * Rendering of the front-end.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.pages.sharing');
	}

	public function back(): mixed
	{
		return $this->redirect(route('livewire-gallery'));
	}

	public function delete(int $id): void
	{
		AccessPermission::query()->where('id', '=', $id)->delete();

		// $this->perms = AccessPermission::with(['album','user'])
		// 	->whereNotNull('user_id')->get()->all();
	}
}
