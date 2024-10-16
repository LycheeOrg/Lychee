<?php

namespace App\Actions\Album;

use App\DTO\AlbumSortingCriterion;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\Collection as NsCollection;

/**
 * @phpstan-type TAlbumSaved array{id:string|null,title:string,original:string,short_title:string,thumb:string}
 */
class ListAlbums extends Action
{
	private const SHORTEN_BY = 80;

	/**
	 * @param int|null    $lft
	 * @param int|null    $rgt
	 * @param string|null $parent_id
	 *
	 * @return TAlbumSaved[]
	 */
	public function do(?int $lft, ?int $rgt, ?string $parent_id): array
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

		/** @var NsCollection<int,string,Album> $albums */
		$albums = $query->get();
		/** @var NsCollection<int,string,Album> $tree */
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
	 * @param NsCollection<int,string,Album>|Collection<int,Album> $collection
	 * @param string                                               $prefix
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
}
