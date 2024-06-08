<?php

namespace App\Livewire\Components\Forms\Album;

use App\DTO\AlbumSortingCriterion;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\Collection as NsCollection;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * TODO: improve speed. 800ms component.
 *
 * @phpstan-type TAlbumSaved array{id:string|null,title:string,original:string,short_title:string,thumb:string}
 */
class SearchAlbum extends Component
{
	private const SHORTEN_BY = 80;

	public ?string $search = null; // ! wired
	/** @var TAlbumSaved[] */
	#[Locked] public array $albumListSaved;
	/**
	 * This is the equivalent of the constructor for Livewire Components.
	 *
	 * @param int|null    $lft       constraint on serach - left
	 * @param int|null    $rgt       constraint on serach - right
	 * @param string|null $parent_id contraint on search remove sub-tree
	 *
	 * @return void
	 */
	public function mount(?string $parent_id, ?int $lft = null, ?int $rgt = null): void
	{
		$this->albumListSaved = $this->getAlbumsListWithPath($lft, $rgt, $parent_id);
	}

	/**
	 * Give the tree of albums owned by the user.
	 *
	 * @return Collection<int,TAlbumSaved>
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
	 * Simply render the form.
	 *
	 * @return View
	 */
	public function render(): View
	{
		return view('livewire.forms.album.search-album');
	}

	/**
	 * @param int|null    $lft
	 * @param int|null    $rgt
	 * @param string|null $parent_id
	 *
	 * @return TAlbumSaved[]
	 */
	private function getAlbumsListWithPath(?int $lft, ?int $rgt, ?string $parent_id): array
	{
		$albumQueryPolicy = resolve(AlbumQueryPolicy::class);
		$unfiltered = $albumQueryPolicy->applyReachabilityFilter(
			// We remove all sub albums
			// Otherwise it would create cyclic dependency
			Album::query()
				->when($lft !== null,
					fn ($q) => $q->where('_lft', '<', $lft)->orWhere('_rgt', '>', $rgt))
		);
		$sorting = AlbumSortingCriterion::createDefault();
		$query = (new SortingDecorator($unfiltered))
			->orderBy($sorting->column, $sorting->order);

		/** @var NsCollection<int,Album> $albums */
		$albums = $query->get();
		/** @var NsCollection<int,Album> $tree */
		$tree = $albums->toTree(null);

		$flat_tree = $this->flatten($tree);

		// Prepend with the possibility to move to root if parent is not already root.
		if ($parent_id !== null) {
			array_unshift(
				$flat_tree,
				[
					'id' => null,
					'title' => __('lychee.ROOT'),
					'original' => __('lychee.ROOT'),
					'short_title' => __('lychee.ROOT'),
					'thumb' => URL::asset('img/no_images.svg'),
				]
			);
		}

		return $flat_tree;
	}

	/**
	 * Flatten the tree and create bread crumb paths.
	 *
	 * @param NsCollection<int,Album>|Collection<int,Album> $collection
	 * @param string                                        $prefix
	 *
	 * @return TAlbumSaved[]
	 */
	private function flatten($collection, $prefix = ''): array
	{
		/** @var TAlbumSaved[] $flatArray */
		$flatArray = [];
		foreach ($collection as $node) {
			$title = $prefix . ($prefix !== '' ? '/' : '') . $node->title;
			$short_title = $this->shorten($title);
			$flatArray[] = [
				'id' => $node->id,
				'title' => $title,
				'original' => $node->title,
				'short_title' => $short_title,
				'thumb' => $node->thumb?->thumbUrl ?? URL::asset('img/no_images.svg'),
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

		if ($len < self::SHORTEN_BY) {
			return $title;
		}
		/** @var Collection<int,string> $title_split */
		$title_split = collect(explode('/', $title));
		$last_elem = $title_split->last();
		$len_last_elem = strlen($last_elem);

		$num_chunks = $title_split->count() - 1;

		if ($num_chunks === 0) {
			return Str::limit($last_elem, self::SHORTEN_BY, '…');
		}

		$title_split = $title_split->take($num_chunks);
		/** @var Collection<int,int> $title_lengths */
		$title_lengths = $title_split->map(fn ($v) => strlen($v));

		// find best target length.

		$len_to_reduce = self::SHORTEN_BY - $len_last_elem - 2 * $num_chunks;
		$unit_target_len = (int) ceil($len_to_reduce / $num_chunks);

		do {
			$unit_target_len--;
			$title_lengths = $title_lengths->map(fn ($v) => $v <= $unit_target_len ? $v : $unit_target_len + 1);
			$resulting_len = $title_lengths->sum();
		} while ($len_to_reduce < $resulting_len);

		$title_split = $title_split->map(fn ($v) => Str::limit($v, $unit_target_len, '…'));

		return implode('/', $title_split->all()) . '/' . $last_elem;
	}

	public function placeholder(): string
	{
		return "<div class='w-full p-4 text-center'>Loading album list...</div>";
	}
}
