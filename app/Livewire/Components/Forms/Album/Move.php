<?php

namespace App\Livewire\Components\Forms\Album;

use App\Actions\Album\Move as MoveAlbums;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\Http\RuleSets\Album\MoveAlbumsRuleSet;
use App\Livewire\Components\Pages\Gallery\Album as GalleryAlbum;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\Collection as NsCollection;
use Livewire\Component;

/**
 * TODO: improve speed. 800ms component.
 */
class Move extends Component
{
	use AuthorizesRequests;
	use UseValidator;
	use Notify;

	private int $shorten_by = 80;

	// We need to use an array instead of directly said album id to reuse the rules.
	/** @var array<int,string> */
	public array $albumIDs;
	public ?string $titleMoved;

	// Destination
	public ?string $albumID = null;
	public ?string $title = null;

	public ?string $search = null; // ! wired

	public array $albumListSaved;

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

		$this->albumListSaved = $this->getAlbumsListWithPath($album);
	}

	/**
	 * Give the tree of albums owned by the user.
	 *
	 * @return Collection<int,Album>
	 */
	public function getAlbumListProperty(): Collection
	{
		$filtered = collect($this->albumListSaved);
		if ($this->search !== null && trim($this->search) !== '') {
			return $filtered->filter(function (array $album) {
				return Str::contains($album['title'], ltrim($this->search), true);
			});
		}

		return $filtered;
	}

	/**
	 * Prepare confirmation step.
	 *
	 * @param string $id
	 * @param string $title
	 *
	 * @return void
	 */
	public function check(string $id, string $title)
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

	private function getAlbumsListWithPath(Album $album): array
	{
		$albumQueryPolicy = resolve(AlbumQueryPolicy::class);
		$unfiltered = $albumQueryPolicy->applyReachabilityFilter(
			// We remove all sub albums
			// Otherwise it would create cyclic dependency
			Album::query()
				->where('_lft', '<', $album->_lft)
				->orWhere('_rgt', '>', $album->_rgt)
		);
		$sorting = AlbumSortingCriterion::createDefault();
		$query = (new SortingDecorator($unfiltered))
			->orderBy($sorting->column, $sorting->order);

		/** @var NsCollection<Album> $albums */
		$albums = $query->get();
		$tree = $albums->toTree(null);

		$flat_tree = $this->flatten($tree);

		// Prepend with the possibility to move to root if parent is not already root.
		if ($album->parent !== null) {
			array_unshift(
				$flat_tree,
				[
					'id' => null,
					'title' => __('lychee.ROOT'),
					'original' => __('lychee.ROOT'),
					'short_title' => __('lychee.ROOT'),
					'thumb' => 'img/no_images.svg',
				]
			);
		}

		return $flat_tree;
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

	// UTILITY FUNCTIONS

	/**
	 * Flatten the tree and create bread crumb paths.
	 *
	 * @param mixed  $collection
	 * @param string $prefix
	 *
	 * @return array
	 */
	private function flatten($collection, $prefix = ''): array
	{
		$flatArray = [];
		foreach ($collection as $node) {
			$title = $prefix . ($prefix !== '' ? '/' : '') . $node->title;
			$short_title = $this->shorten($title);
			$flatArray[] = [
				'id' => $node->id,
				'title' => $title,
				'original' => $node->title,
				'short_title' => $short_title,
				'thumb' => $node->thumb?->thumbUrl ?? 'img/no_images.svg',
			];
			if ($node->children !== null) {
				$flatArray = array_merge($flatArray, $this->flatten($node->children, $title));
				unset($node->children);
			}
		}

		return $flatArray;
	}

	/**
	 * shorten the title to reach a targetted length.
	 *
	 * @param string $title to shorten
	 *
	 * @return string short version with elipsis
	 */
	private function shorten(string $title): string
	{
		$len = strlen($title);

		if ($len < $this->shorten_by) {
			return $title;
		}
		/** @var Collection<int,string> $title_split */
		$title_split = collect(explode('/', $title));
		$last_elem = $title_split->last();
		$len_last_elem = strlen($last_elem);

		$num_chunks = $title_split->count() - 1;

		$title_split = $title_split->take($num_chunks);
		/** @var Collection<int,int> $title_lengths */
		$title_lengths = $title_split->map(fn ($v) => strlen($v));

		// find best target length.

		$len_to_reduce = $this->shorten_by - $len_last_elem - 2 * $num_chunks;
		$unit_target_len = (int) ceil($len_to_reduce / $num_chunks);

		do {
			$unit_target_len--;
			$title_lengths = $title_lengths->map(fn ($v) => $v <= $unit_target_len ? $v : $unit_target_len + 1);
			$resulting_len = $title_lengths->sum();
		} while ($len_to_reduce < $resulting_len);

		$title_split = $title_split->map(fn ($v) => Str::limit($v, $unit_target_len, '…'));

		return implode('/', $title_split->all()) . '/' . $last_elem;
	}
}
