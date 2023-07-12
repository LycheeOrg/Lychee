<?php

namespace App\Http\Livewire\Forms\Album;

use App\Actions\Album\Move as MoveAlbums;
use App\Actions\Albums\Tree;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Factories\AlbumFactory;
use App\Http\Livewire\Traits\Notify;
use App\Http\Livewire\Traits\UseValidator;
use App\Http\RuleSets\Album\MoveAlbumsRuleSet;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class Move extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	// Destination
	public ?string $albumID = null; // ! wired

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

	/**
	 * Give the tree of albums owned by the user.
	 * 
	 * @return Collection<int,Album>
	 */
	public function getAlbumListProperty(): Collection
	{
		$tree = resolve(Tree::class);

		return $tree->get()->albums;
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
		$this->authorize(AlbumPolicy::CAN_EDIT, $album);

		// `findOrFail` returns a union type, but we know that it returns the
		// correct collection in this case
		/** @var Collection<int,Album> $albums */
		$albums = Album::query()->findOrFail($this->albumIDs);
		foreach ($albums as $movedAlbum) {
			$this->authorize(AlbumPolicy::CAN_EDIT, $movedAlbum);
		}

		$move->do($album, $albums);

		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}
