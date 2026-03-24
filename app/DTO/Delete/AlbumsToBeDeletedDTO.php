<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Delete;

use App\Actions\Shop\PurchasableService;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Album;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AlbumsToBeDeletedDTO
{
	/**
	 * Maximum number of IDs to pass in a single whereIn() clause.
	 * MySQL's prepared-statement placeholder limit is 65 535; staying at 1 000
	 * keeps every query well within that bound regardless of query complexity.
	 */
	private const CHUNK_SIZE = 1000;

	/**
	 * Container for all Albums and associated Tracks to be deleted.
	 *
	 * @param string[]                 $parent_ids the parents ID to recompute the cover photo etc
	 * @param string[]                 $album_ids  the IDs of all albums to be deleted (including descendants)
	 * @param array{lft:int,rgt:int}[] $gaps       the gaps to be closed in the nested set tree
	 * @param Collection<string>       $tracks     the Tracks associated to the albums to be deleted
	 *
	 * @return void
	 */
	public function __construct(
		public array $parent_ids,
		public array $album_ids,
		public array $gaps,
		public Collection $tracks,
	) {
	}

	/**
	 * Execute the deletion of albums and associated data.
	 *
	 * @return void
	 */
	public function executeDelete()
	{
		DB::transaction(function (): void {
			// We disable foreign key checks for the duration of the transaction to avoid issues with the complex web of FK constraints among albums,
			// base_albums, and their dependents. The transactional nature ensures that FK checks are re-enabled at the end of the transaction block,
			// even if an exception occurs.
			DB::statement('SET FOREIGN_KEY_CHECKS=0');

			// Safety check: ensure no photos are still linked to any of the albums.
			// Chunk album_ids to avoid hitting the database placeholder limit (MySQL error 1390).
			$has_linked_photos = collect($this->album_ids)->chunk(self::CHUNK_SIZE)->contains(
				fn ($chunk) => DB::table('photo_album')->whereIn('album_id', $chunk->all())->count() > 0
			);
			if ($has_linked_photos) {
				throw new LycheeLogicException('There are still photos linked to the albums to be deleted.');
			}

			$purchasable_service = resolve(PurchasableService::class);
			collect($this->album_ids)
				->chunk(self::CHUNK_SIZE)
				->each(fn ($chunk) => $purchasable_service->deleteMultipleAlbumPurchasables($chunk->all())
				);

			// For the albums table we must delete leaves before their parents to respect
			// the nested-set parent_id foreign key. Load _lft values in chunks, then sort
			// globally by _lft DESC so the deepest leaves come first across all chunks.
			$albums_with_lft = collect($this->album_ids)->chunk(self::CHUNK_SIZE)->reduce(
				function (Collection $carry, Collection $chunk): Collection {
					return $carry->concat(
						DB::table('albums')->whereIn('id', $chunk->all())->select(['id', '_lft'])->get()
					);
				},
				collect([])
			);
			$sorted_album_ids = $albums_with_lft
				->sortByDesc('_lft')
				->pluck('id')
				->values()
				->all();

			// Chunk all subsequent deletes to avoid hitting the placeholder limit.
			// Delete dependents of base_albums first (no ordering constraint among them).
			collect($this->album_ids)->chunk(self::CHUNK_SIZE)->each(function ($chunk): void {
				DB::table('live_metrics')->whereIn('album_id', $chunk->all())->delete();
				DB::table('access_permissions')->whereIn('base_album_id', $chunk->all())->delete();
				DB::table('statistics')->whereIn('album_id', $chunk->all())->delete();
				DB::table('album_size_statistics')->whereIn('album_id', $chunk->all())->delete();
			});

			// Delete albums leaf-first (sorted by _lft DESC) so parent_id FK constraints
			// are never violated when a child still references its parent.
			collect($sorted_album_ids)->chunk(self::CHUNK_SIZE)->each(function ($chunk): void {
				DB::table('albums')->whereIn('id', $chunk->all())->delete();
			});

			// Delete base_albums last: albums.id FK-references base_albums.id, so albums
			// must be fully gone before base_albums rows can be removed.
			collect($this->album_ids)->chunk(self::CHUNK_SIZE)->each(function ($chunk): void {
				DB::table('base_albums')->whereIn('id', $chunk->all())->delete();
			});

			// Now that all albums have been deleted, we need to update the
			// Album table to remove gaps created by the removal.
			$this->removeGaps();

			DB::statement('SET FOREIGN_KEY_CHECKS=1');
		});
	}

	/**
	 * This is ugly as hell and is mostly copy & pasted from
	 * {@link \Kalnoy\Nestedset\NodeTrait} with adoptions.
	 *
	 * @return void
	 */
	private function removeGaps(): void
	{
		// _After all_ albums have been deleted, remove the gaps which
		// have been created by the removed albums.
		// Note, the gaps must be removed beginning with the highest
		// values first otherwise the later indices won't be correct.
		// To save some DB queries, we could implement a "makeMultiGap".
		usort($this->gaps, fn ($a, $b) => $b['lft'] <=> $a['lft']);
		foreach ($this->gaps as $pending_gap) {
			$height = $pending_gap['rgt'] - $pending_gap['lft'] + 1;
			(new Album())->newNestedSetQuery()->makeGap($pending_gap['rgt'] + 1, -$height);
			Album::$actionsPerformed++;
		}
	}
}
