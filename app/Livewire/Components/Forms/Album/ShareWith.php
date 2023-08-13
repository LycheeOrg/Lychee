<?php

namespace App\Livewire\Components\Forms\Album;

use App\Livewire\Forms\SharingAlbumForms;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ShareWith extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	public BaseAlbum $album;
	public SharingAlbumForms $form;

	public ?string $search = null; // ! wired

	public array $albumListSaved;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		$this->album = $album;
		$this->form->setAlbum($album);
		$this->form->setSharing($album->access_permissions()->whereNotNull('user_id')->get());
	}

	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.share-with');
	}
}
