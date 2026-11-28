<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Constants\PhotoAlbum as PA;
use App\DTO\Delete\PhotosToBeDeletedDTO;
use App\Models\Scopes\NotTrashedScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Feature 119 (FR-119-04, DO-119-04): deletes the photos waiting in the
 * trash, in batches of {@see self::BATCH_SIZE}, oldest first, each batch in
 * its own transaction.
 *
 * A trashed photo that has an album link again leaves the trash instead of
 * being deleted (owner rule: a photo goes only with its last link). The
 * others go through {@see PhotosToBeDeletedDTO} (FR-112-14). The file,
 * person-stats and face-embedding jobs of a batch are dispatched after its
 * commit: inline on `sync`, inside the time budget, queued otherwise.
 *
 * A failing batch rolls back alone: its photos stay in the trash for the
 * next run, the error is reported and the run stops.
 */
final class PurgeTrash
{
	public const BATCH_SIZE = 100;

	/** Seconds after which a run on `sync` in an HTTP request starts no new batch (NFR-119-03). */
	public const SYNC_TIME_BUDGET = 10;

	/** Seconds after which a run in a queue worker starts no new batch (worker `--timeout=3600`). */
	public const ASYNC_TIME_BUDGET = 1800;

	/**
	 * @param int|null   $limit       at most this many photos purged, null for no limit
	 * @param float|null $time_budget no batch started after this many seconds, null for no budget
	 */
	public function run(?int $limit = null, ?float $time_budget = null): PurgeTrashResult
	{
		$start = microtime(true);
		$purged = 0;
		$restored = 0;
		$budget_exhausted = false;

		while ($limit === null || $purged < $limit) {
			$size = $limit === null ? self::BATCH_SIZE : min(self::BATCH_SIZE, $limit - $purged);
			$batch = DB::table(NotTrashedScope::TABLE)->orderBy('trashed_at')->orderBy('photo_id')->limit($size)->pluck('photo_id')->all();
			if ($batch === []) {
				break;
			}

			try {
				[$batch_purged, $batch_restored, $jobs] = $this->purgeBatch($batch);
			} catch (\Throwable $e) {
				report($e);
				break;
			}
			$purged += $batch_purged;
			$restored += $batch_restored;
			foreach ($jobs as $job) {
				dispatch($job);
			}

			if ($time_budget !== null && microtime(true) - $start >= $time_budget) {
				$budget_exhausted = true;
				break;
			}
		}

		$left = DB::table(NotTrashedScope::TABLE)->count();
		Log::channel('jobs')->info("Purged {$purged} photos from the trash, {$restored} restored, {$left} left.");

		return new PurgeTrashResult($purged, $restored, $left, $budget_exhausted && $left > 0);
	}

	/**
	 * @param string[] $batch trashed photo ids
	 *
	 * @return array{0:int,1:int,2:array<int,object>} purged count, restored count, jobs to dispatch
	 */
	private function purgeBatch(array $batch): array
	{
		return DB::transaction(function () use ($batch): array {
			$linked = DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $batch)->distinct()->pluck(PA::PHOTO_ID)->all();
			if ($linked !== []) {
				DB::table(NotTrashedScope::TABLE)->whereIn('photo_id', $linked)->delete();
			}

			$to_delete = array_values(array_diff($batch, $linked));
			$jobs = (new PhotosToBeDeletedDTO(force_delete_photo_ids: $to_delete))->executeDelete();
			DB::table(NotTrashedScope::TABLE)->whereIn('photo_id', $to_delete)->delete();

			return [count($to_delete), count($linked), $jobs];
		});
	}
}
