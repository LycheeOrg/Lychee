<?php

declare(strict_types=1);

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\Move as AlbumMove;
use App\Contracts\Models\AbstractAlbum;
use App\Http\RuleSets\Album\MoveAlbumsRuleSet;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MovePanel extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	// We need to use an array instead of directly said album id to reuse the rules.
	/** @var string[] */
	#[Locked] public array $albumIDs;
	#[Locked] public ?string $titleMoved;
	// Destination
	#[Locked] public ?string $albumID = null;
	#[Locked] public ?string $title = null;
	#[Locked] public ?string $parent_id = null;
	#[Locked] public int $lft;
	#[Locked] public int $rgt;
	private AlbumMove $moveAlbums;

	public function boot(): void
	{
		$this->moveAlbums = resolve(AlbumMove::class);
	}

	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param Album $album to update the attributes of
	 *
	 * @return void
	 */
	public function mount(Album $album): void
	{
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);

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
		return view('livewire.forms.album.move-panel');
	}

	/**
	 * Execute transfer of ownership.
	 */
	public function move(): void
	{
		$this->areValid(MoveAlbumsRuleSet::rules());

		// set default for root.
		$this->albumID = $this->albumID === '' ? null : $this->albumID;
		Gate::authorize(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albumIDs]);

		/** @var ?Album $album */
		$album = $this->albumID === null ? null : Album::query()->findOrFail($this->albumID);
		Gate::authorize(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $album]);

		// `findOrFail` returns a union type, but we know that it returns the
		// correct collection in this case
		/** @var Collection<int,Album> $albums */
		$albums = Album::query()->findOrFail($this->albumIDs);
		$this->moveAlbums->do($album, $albums);

		if ($this->parent_id !== null) {
			$this->redirect(route('livewire-gallery-album', ['albumId' => $this->parent_id]), true);
		} else {
			$this->redirect(route('livewire-gallery'), true);
		}
	}
}
