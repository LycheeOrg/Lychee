<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Image;

use App\Enum\FaceScanStatus;
use App\Jobs\DispatchFaceScanJob;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing face detection scans.
 * Centralizes query building and batch dispatch logic.
 */
class FaceDetectionService
{
	/** @var int Number of photo IDs to process per batch */
	private const BATCH_SIZE = 200;

	/**
	 * Count photos that need face scanning (null or failed status).
	 *
	 * @param string|null $album_id optional album to scope the query to
	 *
	 * @return int
	 */
	public function countUnscanedPhotos(?string $album_id = null): int
	{
		return $this->buildUnscanedQuery($album_id)->count();
	}

	/**
	 * Dispatch face scan jobs for unscanned photos (null or failed status).
	 *
	 * @param string|null $album_id optional album to scope the query to
	 *
	 * @return int number of jobs dispatched
	 */
	public function dispatchUnscanedPhotos(?string $album_id = null): int
	{
		return $this->dispatchForQuery($this->buildUnscanedQuery($album_id));
	}

	/**
	 * Dispatch face scan jobs for specific photos or all photos in an album.
	 *
	 * @param string[]|null $photo_ids specific photo IDs to scan, or null to scan all in album
	 * @param string|null   $album_id  album ID to scan (required if photo_ids is null)
	 * @param bool          $force     if true, rescan even if photo has assigned faces
	 *
	 * @return int number of jobs dispatched
	 */
	public function dispatchPhotos(?array $photo_ids, ?string $album_id, bool $force = false): int
	{
		$query = Photo::query()->select('id');

		if ($photo_ids !== null) {
			$query->whereIn('id', $photo_ids);
		} else {
			$query->whereHas('albums', fn ($q) => $q->where('albums.id', '=', $album_id));
		}

		if (!$force) {
			// Skip photos that have at least one face with a person assigned
			$query->whereDoesntHave('faces', fn ($q) => $q->whereNotNull('person_id'));
		}

		return $this->dispatchForQuery($query);
	}

	/**
	 * Build query for photos with null or failed face scan status.
	 *
	 * @param string|null $album_id optional album to scope the query to
	 *
	 * @return Builder<Photo>
	 */
	private function buildUnscanedQuery(?string $album_id = null): Builder
	{
		$query = Photo::query()
			->select('id')
			->whereNull('face_scan_status')
			->orWhere('face_scan_status', '=', FaceScanStatus::FAILED->value);

		if ($album_id !== null) {
			$query->whereHas('albums', fn ($q) => $q->where('albums.id', '=', $album_id));
		}

		return $query;
	}

	/**
	 * Dispatch face scan jobs for all photos matching the given query.
	 * Updates photos to PENDING status and dispatches DispatchFaceScanJob.
	 *
	 * @param Builder<Photo> $query
	 *
	 * @return int number of jobs dispatched
	 */
	private function dispatchForQuery(Builder $query): int
	{
		$dispatched = 0;

		$query->lazyById(self::BATCH_SIZE, 'id')
			->chunk(self::BATCH_SIZE)
			->each(function ($chunk) use (&$dispatched): void {
				$ids = $chunk->pluck('id')->all();

				// Set status to PENDING in bulk
				Photo::whereIn('id', $ids)->update([
					'face_scan_status' => FaceScanStatus::PENDING->value,
				]);

				// Dispatch a job for each photo
				foreach ($ids as $photo_id) {
					DispatchFaceScanJob::dispatch($photo_id);
					$dispatched++;
				}
			});

		Log::info(__CLASS__ . " — dispatched {$dispatched} face scan jobs.");

		return $dispatched;
	}
}
