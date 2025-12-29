<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\PhotoAlbum as PA;
use App\Models\Album;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recomputes all computed fields for a single album and propagates to parent.
 *
 * Computed fields:
 * - max_taken_at, min_taken_at
 * - num_children, num_photos
 * - auto_cover_id_max_privilege, auto_cover_id_least_privilege
 */
class RecomputeAlbumStatsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	/**
	 * Number of times to retry the job.
	 *
	 * @var int
	 */
	public $tries = 3;

	/**
	 * @param string $album_id The ID of the album to recompute
	 */
	public function __construct(
		public string $album_id,
	) {
	}

	/**
	 * Get the middleware the job should pass through.
	 *
	 * @return array<int,object>
	 */
	public function middleware(): array
	{
		return [
			(new WithoutOverlapping($this->album_id))->releaseAfter(60),
		];
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		Log::info("Recomputing stats for album {$this->album_id}");

		try {
			DB::transaction(function () {
				$album = Album::query()->findOrFail($this->album_id);

				// Compute counts
				$album->num_children = $this->computeNumChildren($album);
				$album->num_photos = $this->computeNumPhotos($album);

				// Compute date range
				$dates = $this->computeTakenAtRange($album);
				$album->min_taken_at = $dates['min'];
				$album->max_taken_at = $dates['max'];

				// Compute cover IDs (simplified for now - will be enhanced in I3)
				$album->auto_cover_id_max_privilege = $this->computeMaxPrivilegeCover($album);
				$album->auto_cover_id_least_privilege = $this->computeLeastPrivilegeCover($album);

				// Save without dispatching events to avoid infinite loop
				$album->saveQuietly();

				// Propagate to parent if exists
				if ($album->parent_id !== null) {
					Log::info("Propagating to parent {$album->parent_id}");
					self::dispatch($album->parent_id);
				}
			});
		} catch (\Exception $e) {
			Log::error("Propagation stopped at album {$this->album_id} due to failure: " . $e->getMessage());

			throw $e;
		}
	}

	/**
	 * Compute num_children (count of direct children).
	 *
	 * @param Album $album
	 *
	 * @return int
	 */
	private function computeNumChildren(Album $album): int
	{
		return DB::table('albums')
			->where('parent_id', '=', $album->id)
			->count();
	}

	/**
	 * Compute num_photos (count of photos directly in this album, not descendants).
	 *
	 * @param Album $album
	 *
	 * @return int
	 */
	private function computeNumPhotos(Album $album): int
	{
		return DB::table('photos')
			->join(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)
			->where(PA::ALBUM_ID, '=', $album->id)
			->count();
	}

	/**
	 * Compute min_taken_at and max_taken_at.
	 *
	 * Uses nested set JOIN to include photos from this album and all descendants.
	 *
	 * @param Album $album
	 *
	 * @return array{min: \Carbon\Carbon|null, max: \Carbon\Carbon|null}
	 */
	private function computeTakenAtRange(Album $album): array
	{
		$result = DB::table('albums', 'a')
			->join(PA::PHOTO_ALBUM, 'a.id', '=', PA::ALBUM_ID)
			->join('photos', PA::PHOTO_ID, '=', 'photos.id')
			->where('a._lft', '>=', $album->_lft)
			->where('a._rgt', '<=', $album->_rgt)
			->whereNotNull('photos.taken_at')
			->selectRaw('MIN(photos.taken_at) as min_taken_at, MAX(photos.taken_at) as max_taken_at')
			->first();

		return [
			'min' => $result?->min_taken_at ? new \Carbon\Carbon($result->min_taken_at) : null,
			'max' => $result?->max_taken_at ? new \Carbon\Carbon($result->max_taken_at) : null,
		];
	}

	/**
	 * Check if album is in NSFW context (album or any parent is NSFW).
	 *
	 * @param Album $album
	 *
	 * @return bool
	 */
	private function isInNSFWContext(Album $album): bool
	{
		$count = DB::table('base_albums')
			->where('is_nsfw', '=', true)
			->where('_lft', '<=', $album->_lft)
			->where('_rgt', '>=', $album->_rgt)
			->count();

		return $count > 0;
	}

	/**
	 * Compute max-privilege cover (admin/owner view).
	 *
	 * Selects best photo from album + descendants with NO access filters.
	 * Applies NSFW context: if album/parent is NSFW, allow NSFW photos; else exclude.
	 * Ordering: is_starred DESC, then taken_at DESC, then id ASC.
	 *
	 * @param Album $album
	 *
	 * @return string|null
	 */
	private function computeMaxPrivilegeCover(Album $album): ?string
	{
		$is_nsfw_context = $this->isInNSFWContext($album);

		$query = DB::table('albums', 'a')
			->join(PA::PHOTO_ALBUM, 'a.id', '=', PA::ALBUM_ID)
			->join('photos', PA::PHOTO_ID, '=', 'photos.id')
			->where('a._lft', '>=', $album->_lft)
			->where('a._rgt', '<=', $album->_rgt);

		// Apply NSFW filtering based on context
		if (!$is_nsfw_context) {
			// Not in NSFW context - exclude photos that belong to NSFW albums
			// A photo is NSFW if it belongs to any album marked as is_nsfw=true
			$query->whereNotExists(function (BaseBuilder $q) use ($album) {
				$q->select(DB::raw(1))
					->from('albums as nsfw_album')
					->join(PA::PHOTO_ALBUM . ' as pa_nsfw', 'nsfw_album.id', '=', 'pa_nsfw.album_id')
					->whereColumn('pa_nsfw.photo_id', '=', 'photos.id')
					->where('nsfw_album.is_nsfw', '=', true)
					->where('nsfw_album._lft', '>=', $album->_lft)
					->where('nsfw_album._rgt', '<=', $album->_rgt);
			});
		}

		$result = $query
			->orderByDesc('photos.is_starred')
			->orderByDesc('photos.taken_at')
			->orderBy('photos.id', 'asc')
			->select('photos.id')
			->first();

		return $result?->id;
	}

	/**
	 * Compute least-privilege cover (public view).
	 *
	 * Selects best photo from album + descendants WITH access control filters.
	 * Only includes photos visible to all users (public photos).
	 * Applies NSFW context: if album/parent is NSFW, allow NSFW photos; else exclude.
	 * Ordering: is_starred DESC, then taken_at DESC, then id ASC.
	 *
	 * @param Album $album
	 *
	 * @return string|null
	 */
	private function computeLeastPrivilegeCover(Album $album): ?string
	{
		$is_nsfw_context = $this->isInNSFWContext($album);
		$photo_query_policy = resolve(PhotoQueryPolicy::class);
		$album_query_policy = resolve(AlbumQueryPolicy::class);
		$album_lft = $album->_lft;
		$album_rgt = $album->_rgt;

		$query = DB::table('albums', 'a')
			->join(PA::PHOTO_ALBUM, 'a.id', '=', PA::ALBUM_ID)
			->join('photos', PA::PHOTO_ID, '=', 'photos.id')
			->where('a._lft', '>=', $album_lft)
			->where('a._rgt', '<=', $album_rgt);

		// Apply access control filters for public photos only
		$query->where(function (BaseBuilder $q) use ($photo_query_policy, $album_lft, $album_rgt) {
			$photo_query_policy->appendSearchabilityConditions($q, $album_lft, $album_rgt);
		});

		// Apply album accessibility conditions
		$query->where(function (BaseBuilder $q) use ($album_query_policy) {
			$album_query_policy->appendAccessibilityConditions($q);
		});

		// Apply NSFW filtering based on context
		if (!$is_nsfw_context) {
			// Not in NSFW context - exclude photos that belong to NSFW albums
			// A photo is NSFW if it belongs to any album marked as is_nsfw=true
			$query->whereNotExists(function (BaseBuilder $q) use ($album) {
				$q->select(DB::raw(1))
					->from('albums as nsfw_album')
					->join(PA::PHOTO_ALBUM . ' as pa_nsfw', 'nsfw_album.id', '=', 'pa_nsfw.album_id')
					->whereColumn('pa_nsfw.photo_id', '=', 'photos.id')
					->where('nsfw_album.is_nsfw', '=', true)
					->where('nsfw_album._lft', '>=', $album->_lft)
					->where('nsfw_album._rgt', '<=', $album->_rgt);
			});
		}

		$result = $query
			->orderByDesc('photos.is_starred')
			->orderByDesc('photos.taken_at')
			->orderBy('photos.id', 'asc')
			->select('photos.id')
			->first();

		return $result?->id;
	}

	/**
	 * Handle job failure after all retries exhausted.
	 *
	 * @param \Throwable $exception
	 *
	 * @return void
	 */
	public function failed(\Throwable $exception): void
	{
		Log::error("Job failed permanently for album {$this->album_id}: " . $exception->getMessage());
		// Do NOT dispatch parent job on failure - propagation stops here
	}
}
