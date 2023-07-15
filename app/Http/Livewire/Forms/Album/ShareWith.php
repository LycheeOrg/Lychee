<?php

namespace App\Http\Livewire\Forms\Album;

use App\Http\Livewire\Traits\Notify;
use App\Http\Livewire\Traits\UseValidator;
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
