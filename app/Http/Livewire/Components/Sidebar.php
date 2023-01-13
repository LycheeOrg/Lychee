<?php

namespace App\Http\Livewire\Components;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Livewire\Components\Base\Openable;
use App\Http\Livewire\Traits\AlbumProperty;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\View\View;

/**
 * Sidebar, this contains the info of the current Photo or Album
 * This extends from Openable as it is either visible or hidden.
 */
class Sidebar extends Openable
{
	use AlbumProperty;

	public ?Photo $photo = null;
	public ?BaseAlbum $baseAlbum = null;
	public ?BaseSmartAlbum $smartAlbum = null;

	/**
	 * Given an Album or a Photo load the data.
	 *
	 * @param AbstractAlbum|null $album
	 * @param Photo|null         $photo
	 *
	 * @return void
	 */
	public function mount(?AbstractAlbum $album = null, ?Photo $photo = null): void
	{
		$this->loadAlbum($album);
		if ($this->photo !== null) {
			$this->photo = $photo;
		}
	}

	/**
	 * render the view.
	 * This calls in the blade side one of the following component:
	 * - app/Http/Livewire/Sidebar/Album
	 * - or app/Http/Livewire/Sidebar/Photo.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.sidebar');
	}
}
