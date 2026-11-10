<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Album;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Keeps `albums.is_recursive_nsfw` equal to "the album or one of its
 * ancestors has `is_nsfw`" (Feature 103, ADR-103-01).
 *
 * The flag is derived data: every writer of `base_albums.is_nsfw` or of the
 * album tree calls this explicitly once its change is saved.
 */
class SyncRecursiveNsfw
{
	/**
	 * Recomputes the flag of every album: an album is flagged when it lies in
	 * the nested-set range of an NSFW album. Only the flags that change are
	 * written, so a sync also repairs any flag that went stale.
	 */
	public function do(): void
	{
		$ranges = self::outermostRanges(DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('base_albums.is_nsfw', '=', true)
			->get(['albums._lft', 'albums._rgt'])
			->map(fn (object $row) => [(int) $row->_lft, (int) $row->_rgt])
			->all());

		DB::transaction(function () use ($ranges): void {
			DB::table('albums')
				->where('is_recursive_nsfw', '=', true)
				->whereNot(fn (Builder $q) => self::whereInRanges($q, $ranges))
				->update(['is_recursive_nsfw' => false]);
			DB::table('albums')
				->where('is_recursive_nsfw', '=', false)
				->where(fn (Builder $q) => self::whereInRanges($q, $ranges))
				->update(['is_recursive_nsfw' => true]);
		});
	}

	/**
	 * Whether the album with these nested-set bounds, or one of its
	 * ancestors, is NSFW: the value its flag must have. Read from
	 * `base_albums.is_nsfw` along the tree, never from stored flags.
	 */
	public function isRecursiveNsfw(int $left, int $right): bool
	{
		return DB::table('albums')
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('albums._lft', '<=', $left)
			->where('albums._rgt', '>=', $right)
			->where('base_albums.is_nsfw', '=', true)
			->exists();
	}

	/**
	 * The ranges not contained in another one of the list, ordered by `_lft`.
	 *
	 * Nested-set ranges never overlap partially, so a range starting before
	 * the right bound of the last kept one lies inside it.
	 *
	 * @param array<int,array{0:int,1:int}> $ranges `[_lft, _rgt]` pairs
	 *
	 * @return array<int,array{0:int,1:int}>
	 */
	public static function outermostRanges(array $ranges): array
	{
		usort($ranges, fn (array $a, array $b) => $a[0] <=> $b[0]);

		$outermost = [];
		$right = 0;
		foreach ($ranges as $range) {
			if ($range[0] > $right) {
				$outermost[] = $range;
				$right = $range[1];
			}
		}

		return $outermost;
	}

	/**
	 * Albums inside one of `$ranges`. The leading `1 = 0` makes an empty list
	 * match no album (and its negation every album).
	 *
	 * @param array<int,array{0:int,1:int}> $ranges
	 */
	private static function whereInRanges(Builder $query, array $ranges): void
	{
		$query->whereRaw('1 = 0');
		foreach ($ranges as [$left, $right]) {
			$query->orWhere(fn (Builder $q) => $q->where('_lft', '>=', $left)->where('_rgt', '<=', $right));
		}
	}
}
