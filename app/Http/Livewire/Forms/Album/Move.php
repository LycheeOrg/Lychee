<?php

namespace App\Http\Livewire\Forms\Album;

use App\Actions\Album\Move as MoveAlbums;
use App\DTO\AlbumSortingCriterion;
use App\Http\Livewire\Traits\Notify;
use App\Http\Livewire\Traits\UseValidator;
use App\Http\RuleSets\Album\MoveAlbumsRuleSet;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
		$this->albumID = '';
		$this->albumIDs = [$album->id];
		$this->albumListSaved = $this->getAlbumsListWithPath();
	}

	/**
	 * Give the tree of albums owned by the user.
	 *
	 * @return Collection<int,Album>
	 */
	public function getAlbumListProperty(): Collection
	{
		$filtered = collect($this->albumListSaved);
		if ($this->search !== null) {
			return $filtered->filter(function (array $album) {
				return Str::contains($album['title'], $this->search);
			});
		}

		return $filtered;
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

	private function getAlbumsListWithPath(): array
	{
		$albumQueryPolicy = resolve(AlbumQueryPolicy::class);
		$unfiltered = $albumQueryPolicy->applyReachabilityFilter(Album::query());
		$sorting = AlbumSortingCriterion::createDefault();
		$query = (new SortingDecorator($unfiltered))
			->orderBy($sorting->column, $sorting->order);

		/** @var NsCollection<Album> $albums */
		$albums = $query->get();
		$tree = $albums->toTree(null);
		foreach ($tree as $branches) {
			$this->breadCrumbPath($branches);
		}

		return $this->flatten($tree);
	}

	private function breadCrumbPath(ALbum $album, string $prefix = '')
	{
		$album->title = $prefix . ($prefix !== '' ? '/' : '') . $album->title;
		if ($album->num_children === 0) {
			return;
		}

		foreach ($album->children as $child) {
			$this->breadCrumbPath($child, $album->title);
		}
	}

	private function flatten($collection): array
	{
		$flatArray = [];
		foreach ($collection as $key => $node) {
			$flatArray[] = ['id' => $node->id, 'title' => $node->title];
			if ($node->children !== null) {
				$flatArray = array_merge($flatArray, $this->flatten($node->children));
				unset($node->children);
			}
		}

		return $flatArray;
	}
	// /**
	//  * Creates the breadcrumb path of an album.
	//  *
	//  * @param \App\Models\Album $album this is not really an album but a very
	//  *                                 stripped down version of an album with
	//  *                                 only the following properties:
	//  *                                 `title`, `parent` and `parent_id` (unused here)
	//  *
	//  * @return string the breadcrumb path
	//  */
	// private function breadcrumbPath(object $album): string
	// {
	// 	$title = [$album->title];
	// 	$parent = $album->parent;
	// 	while ($parent !== null) {
	// 		array_unshift($title, $parent->title);
	// 		$parent = $parent->parent;
	// 	}

	// 	return implode('/', $title);
	// }
}
