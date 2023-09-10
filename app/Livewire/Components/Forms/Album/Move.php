<?php

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\Move as MoveAlbums;
use App\Contracts\Models\AbstractAlbum;
use App\Http\RuleSets\Album\MoveAlbumsRuleSet;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Move extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	// We need to use an array instead of directly said album id to reuse the rules.
	/** @var array<int,string> */
	#[Locked] public array $albumIDs;
	#[Locked] public ?string $titleMoved;
	// Destination
	#[Locked] public ?string $albumID = null;
	#[Locked] public ?string $title = null;
	#[Locked] public ?string $parent_id;
	#[Locked] public int $lft;
	#[Locked] public int $rgt;
	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param Album $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(Album $album): void
	{
		$this->albumIDs = [$album->id];
		$this->titleMoved = $album->title;
		$this->lft = $album->_lft;
		$this->rgt = $album->_rgt;
		$this->parent_id = $album->parent_id;
	}

	/**
	 * Prepare confirmation step.
	 *
	 * @param string $id
	 * @param string $title
	 *
	 * @return void
	 */
	public function setAlbum(string $id, string $title)
	{
		$this->albumID = $id;
		$this->title = $title;
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

	/**
	 * Execute transfer of ownership.
	 *
	 * @param MoveAlbums $move
	 */
	public function move(MoveAlbums $move): void
	{
		$this->areValid(MoveAlbumsRuleSet::rules());

		// set default for root.
		$this->albumID = $this->albumID === '' ? null : $this->albumID;

		/** @var ?Album $album */
		$album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);
		$this->authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);

		// `findOrFail` returns a union type, but we know that it returns the
		// correct collection in this case
		/** @var Collection<int,Album> $albums */
		$albums = Album::query()->findOrFail($this->albumIDs);
		foreach ($albums as $movedAlbum) {
			$this->authorize(AlbumPolicy::CAN_EDIT, $movedAlbum);
		}

		$move->do($album, $albums);

		$this->dispatch('toggleAlbumDetails')->to(GalleryAlbum::class);
		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}
