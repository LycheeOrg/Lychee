<?php

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\Move as AlbumMove;
use App\Contracts\Livewire\Params;
use App\Contracts\Models\AbstractAlbum;
use App\Factories\AlbumFactory;
use App\Http\RuleSets\Album\MoveAlbumsRuleSet;
use App\Livewire\Traits\InteractWithModal;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Move extends Component
{
	use InteractWithModal;
	use AuthorizesRequests;

	#[Locked] public string $parent_id;
	/** @var array<int,string> */
	#[Locked] public array $albumIDs;
	#[Locked] public string $titleMoved = '';
	#[Locked] public int $num;
	// Destination
	#[Locked] public ?string $albumID = null;
	#[Locked] public ?string $title = null;
	#[Locked] public int $lft;
	#[Locked] public int $rgt;
	private AlbumFactory $albumFactory;
	private AlbumMove $moveAlbums;

	public function boot(): void
	{
		$this->albumFactory = resolve(AlbumFactory::class);
		$this->moveAlbums = resolve(AlbumMove::class);
	}

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param array{albumID?:string,albumIDs?:array<int,string>,parentID:?string} $params to move
	 *
	 * @return void
	 */
	public function mount(array $params = ['parentID' => null]): void
	{
		$id = $params[Params::ALBUM_ID] ?? null;
		if ($id !== null) {
			$this->albumIDs = [$id];
			/** @var Album $album */
			$album = $this->albumFactory->findBaseAlbumOrFail($id, false);
			$this->titleMoved = $album->title;
			$this->lft = $album->_lft;
			$this->rgt = $album->_rgt;
		} else {
			$this->albumIDs = $params[Params::ALBUM_IDS] ?? [];
		}

		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albumIDs]);
		$this->parent_id = $params[Params::PARENT_ID] ?? null;

		// TODO : This could be dangerous: it is possible to put an album within itself!
		// Some safety, but not perfect.
		if ($this->parent_id !== null && $id !== null) {
			/** @var Album $album */
			$album = $this->albumFactory->findBaseAlbumOrFail($id, false);
			$this->lft = $album->_lft;
			$this->rgt = $album->_rgt;
		}
		$this->num = count($this->albumIDs);
	}

	/**
	 * Prepare confirmation step.
	 *
	 * @param string $id
	 * @param string $title
	 *
	 * @return void
	 */
	public function setAlbum(string $id, string $title): void
	{
		$this->albumID = $id;
		$this->title = $title;
	}

	public function submit(): void
	{
		$this->albumID = $this->albumID === '' ? null : $this->albumID;

		$this->validate(MoveAlbumsRuleSet::rules());
		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albumIDs]);

		/** @var ?Album $album */
		$album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);

		// `findOrFail` returns a union type, but we know that it returns the
		// correct collection in this case
		/** @var Collection<int,Album> $albums */
		$albums = Album::query()->findOrFail($this->albumIDs);
		$this->moveAlbums->do($album, $albums);

		$this->redirect(route('livewire-gallery-album', ['albumId' => $this->parent_id]), true);
		$this->close();
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
	 * Add an handle to close the modal form from a user-land call.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->closeModal();
	}
}
