<?php

namespace App\Http\Livewire\Forms\Album;

use App\Actions\Albums\Tree;
use App\Http\Livewire\Traits\Notify;
use App\Http\Livewire\Traits\UseValidator;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Move extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	// Destination
	public string $albumID;
	
	// We need to use an array instead of directly said album id to reuse the rules.
	/** @var array<int,string> */
	public array $albumIDs;

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param BaseAlbum $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(BaseAlbum $album): void
	{
		$this->albumID = '';
		$this->albumIDs = [$album->id];
	}

	public function getAlbumListProperty() : array {
		$tree = resolve(Tree::class);
		return $tree->get()->toArray(null);
	}
	/**
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.move');
	}
}
