<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
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
use Kalnoy\Nestedset\Collection as NsCollection;

/**
 * @phpstan-type TAlbumSaved array{id:string|null,title:string,original:string,short_title:string,thumb:string}
 */
class ListAlbums extends Action
{
	private const SHORTEN_BY = 80;

	/**
	 * @param Collection<int,Album> $albumsFiltering
	 * @param string|null           $parent_id
	 *
	 * @return TAlbumSaved[]
	 */
	public function do(Collection $albumsFiltering, ?string $parentId): array
	{
		$albumQueryPolicy = resolve(AlbumQueryPolicy::class);
		$unfiltered = $albumQueryPolicy->applyReachabilityFilter(
			// We remove all sub albums
			// Otherwise it would create cyclic dependency
			Album::query()
				->when($albumsFiltering->count() > 0,
					function ($q) use ($albumsFiltering) {
						$albumsFiltering->each(
							fn ($a) => $q->whereNot(fn ($q1) => $q1->where('_lft', '>=', $a->_lft)->where('_rgt', '<=', $a->_rgt))
						);

						return $q;
					})
		);
		$sorting = AlbumSortingCriterion::createDefault();
		$query = (new SortingDecorator($unfiltered))
			->orderBy($sorting->column, $sorting->order);

		/** @var NsCollection<Album> $albums */
		$albums = $query->get();
		/** @var NsCollection<Album> $tree */
		$tree = $albums->toTree(null);

		$flatTree = $this->flatten($tree);

		// Prepend with the possibility to move to root if parent is not already root.
		if ($parentId !== null) {
			array_unshift(
				$flatTree,
				[
					'id' => null,
					'title' => __('gallery.root'),
					'original' => __('gallery.root'),
					'short_title' => __('gallery.root'),
					'thumb' => URL::asset('img/no_images.svg'),
				]
			);
		}

		return $flatTree;
	}

	/**
	 * Flatten the tree and create bread crumb paths.
	 *
	 * @param NsCollection<Album>|Collection<int,Album> $collection
	 * @param string                                    $prefix
	 *
	 * @return TAlbumSaved[]
	 */
	private function flatten($collection, $prefix = ''): array
	{
		/** @var TAlbumSaved[] $flatArray */
		$flatArray = [];
		foreach ($collection as $node) {
			$title = $prefix . ($prefix !== '' ? '/' : '') . $node->title;
			$shortTitle = $this->shorten($title);
			$flatArray[] = [
				'id' => $node->id,
				'title' => $title,
				'original' => $node->title,
				'short_title' => $shortTitle,
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

		if ($len <= self::SHORTEN_BY) {
			return $title;
		}
		/** @var Collection<int,string> $title_split */
		$titleSplit = collect(explode('/', $title));
		$lastElem = $titleSplit->last();
		$lenLastElem = strlen($lastElem);

		$numChunks = $titleSplit->count() - 1;

		if ($numChunks === 0) {
			return Str::limit($lastElem, self::SHORTEN_BY, '…');
		}

		$titleSplit = $titleSplit->take($numChunks);
		/** @var Collection<int,int> $title_lengths */
		$titleLengths = $titleSplit->map(fn ($v) => strlen($v));

		// find best target length.

		$lenToReduce = self::SHORTEN_BY - $lenLastElem - 2 * $numChunks;
		$unitTargetLen = (int) ceil($lenToReduce / $numChunks);

		do {
			$unitTargetLen--;
			$titleLengths = $titleLengths->map(fn ($v) => $v <= $unitTargetLen ? $v : $unitTargetLen + 1);
			$resultingLen = $titleLengths->sum();
		} while ($lenToReduce < $resultingLen);

		$titleSplit = $titleSplit->map(fn ($v) => Str::limit($v, $unitTargetLen > 0 ? $unitTargetLen : 0, '…'));

		return implode('/', $titleSplit->all()) . '/' . $lastElem;
	}
}
