<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\DTO\AlbumSortingCriterion;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\Contracts\NestedSetCollection;

/**
 * @phpstan-type TAlbumSaved array{id:string|null,title:string,original:string,short_title:string,thumb:string}
 */
class ListAlbums
{
	private const SHORTEN_BY = 80;

	/**
	 * @return TAlbumSaved[]
	 */
	public function do(Collection $albums_filtering, ?string $parent_id): array
	{
		$album_query_policy = resolve(AlbumQueryPolicy::class);
		$unfiltered = $album_query_policy->applyReachabilityFilter(
			// We remove all sub albums
			// Otherwise it would create cyclic dependency
			Album::query()
				->when($albums_filtering->count() > 0,
					function ($q) use ($albums_filtering) {
						$albums_filtering->each(
							fn ($a) => $q->whereNot(fn ($q1) => $q1->where('_lft', '>=', $a->_lft)->where('_rgt', '<=', $a->_rgt))
						);

						return $q;
					})
		);
		$sorting = AlbumSortingCriterion::createDefault();
		$query = (new SortingDecorator($unfiltered))
			->orderBy($sorting->column, $sorting->order);

		/** @var NestedSetCollection<Album> $albums */
		/** @phpstan-ignore varTag.nativeType (False positive, NestedSetCollection requires Eloquent Collection) */
		$albums = $query->get();
		/** @var NestedSetCollection<Album> $tree */
		$tree = $albums->toTree(null);

		$flat_tree = $this->flatten($tree);

		// Prepend with the possibility to move to root if parent is not already root.
		if ($parent_id !== null) {
			array_unshift(
				$flat_tree,
				[
					'id' => null,
					'title' => __('gallery.root'),
					'original' => __('gallery.root'),
					'short_title' => __('gallery.root'),
					'thumb' => URL::asset('img/no_images.svg'),
				]
			);
		}

		return $flat_tree;
	}

	/**
	 * Flatten the tree and create bread crumb paths.
	 *
	 * @param NestedSetCollection<Album>|Collection<int,Album> $collection
	 * @param string                                           $prefix
	 *
	 * @return TAlbumSaved[]
	 */
	private function flatten($collection, $prefix = ''): array
	{
		/** @var TAlbumSaved[] $flat_array */
		$flat_array = [];
		foreach ($collection as $node) {
			$title = $prefix . ($prefix !== '' ? '/' : '') . $node->title;
			$short_title = $this->shorten($title);
			$flat_array[] = [
				'id' => $node->id,
				'title' => $title,
				'original' => $node->title,
				'short_title' => $short_title,
				'thumb' => $node->thumb?->thumbUrl ?? URL::asset('img/no_images.svg'),
			];
			if ($node->children !== null) {
				$flat_array = array_merge($flat_array, $this->flatten($node->children, $title));
				unset($node->children);
			}
		}

		return $flat_array;
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

		if ($len <= self::SHORTEN_BY) {
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
		$title_lengths = $title_split->map(fn ($v) => strlen($v));

		// find best target length.

		$len_to_reduce = self::SHORTEN_BY - $len_last_elem - 2 * $num_chunks;
		$unit_target_len = (int) ceil($len_to_reduce / $num_chunks);

		do {
			$unit_target_len--;
			$title_lengths = $title_lengths->map(fn ($v) => $v <= $unit_target_len ? $v : $unit_target_len + 1);
			$resulting_len = $title_lengths->sum();
		} while ($len_to_reduce < $resulting_len);

		$title_split = $title_split->map(fn ($v) => Str::limit($v, $unit_target_len > 0 ? $unit_target_len : 0, '…'));

		return implode('/', $title_split->all()) . '/' . $last_elem;
	}
}