<?php

namespace App\Livewire\Components\Forms\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\AccessPermission;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShareWithLine extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	public AccessPermission $perm;

	#[Locked] public string $album_title;
	#[Locked] public string $username;
	public bool $grants_full_photo_access;
	public bool $grants_download;
	public bool $grants_upload;
	public bool $grants_edit;
	public bool $grants_delete;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param AccessPermission $perm
	 *
	 * @return void
	 */
	public function mount(AccessPermission $perm, string $album_title = ''): void
	{
		$this->album_title = $album_title;
		$this->perm = $perm;
		$this->username = $perm->user->username;
		$this->grants_full_photo_access = $perm->grants_full_photo_access;
		$this->grants_download = $perm->grants_download;
		$this->grants_upload = $perm->grants_upload;
		$this->grants_edit = $perm->grants_edit;
		$this->grants_delete = $perm->grants_delete;
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.share-with-line');
	}

	/**
	 * This runs after a wired property is updated.
	 *
	 * @param mixed $field
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function updated($field, $value): void
	{
		Gate::authorize(AlbumPolicy::IS_OWNER, [AbstractAlbum::class, $this->perm->album]);

		$this->perm->grants_full_photo_access = $this->grants_full_photo_access;
		$this->perm->grants_download = $this->grants_download;
		$this->perm->grants_upload = $this->grants_upload;
		$this->perm->grants_edit = $this->grants_edit;
		$this->perm->grants_delete = $this->grants_delete;
		$this->perm->save();
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}
