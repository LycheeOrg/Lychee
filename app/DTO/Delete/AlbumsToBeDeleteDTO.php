<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Delete;

use App\Actions\Shop\PurchasableService;
use App\Models\Album;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AlbumsToBeDeleteDTO
{
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
			$purchasable_service = resolve(PurchasableService::class);
			$purchasable_service->deleteMultipleAlbumPurchasables($this->album_ids);
			DB::table('live_metrics')->whereIn('album_id', $this->album_ids)->delete();
			DB::table('access_permissions')->whereIn('base_album_id', $this->album_ids)->delete();
			DB::table('statistics')->whereIn('album_id', $this->album_ids)->delete();
			DB::table('album_size_statistics')->whereIn('album_id', $this->album_ids)->delete();
			DB::table('albums')->whereIn('id', $this->album_ids)->orderBy('_lft', 'desc')->delete();
			DB::table('base_albums')->whereIn('id', $this->album_ids)->delete();

			// Now that all albums have been deleted, we need to update the
			// Album table to remove gaps created by the removal.
			$this->removeGaps();
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
