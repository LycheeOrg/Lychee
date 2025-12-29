<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\PhotoAlbum as PA;
use App\Constants\AccessPermissionConstants as APC;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Policies\PhotoQueryPolicy;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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

	private string $jobId;

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
		$this->jobId = uniqid('job_', true);

		// Register this as the latest job for this album
		Cache::put(
			'album_stats_latest_job:' . $this->album_id,
			$this->jobId
		);
	}

	/**
	 * Get the middleware the job should pass through.
	 *
	 * @return array<int,object>
	 */
	public function middleware(): array
	{
		return [
			Skip::when(fn () => $this->hasNewerJobQueued()),
		];
	}

	protected function hasNewerJobQueued(): bool
	{
		$cache_key = 'album_stats_latest_job:' . $this->album_id;
		$latest_job_id = Cache::get($cache_key);

		return $latest_job_id !== $this->jobId || $latest_job_id === null;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		Log::info("Recomputing stats for album {$this->album_id}");
		Cache::forget("album_stats_latest_job:{$this->album_id}");

		try {
			DB::transaction(function (): void {
				$album = Album::query()->addVirtualIsRecursiveNSFW()->findOrFail($this->album_id);

				// Get recursive NSFW status
				$is_nsfw_context = $album->is_recursive_nsfw;

				// Compute counts
				$album->num_children = $this->computeNumChildren($album);
				$album->num_photos = $this->computeNumPhotos($album);

				// Compute date range
				$dates = $this->computeTakenAtRange($album);
				$album->min_taken_at = $dates['min'];
				$album->max_taken_at = $dates['max'];

				// Compute cover IDs (simplified for now - will be enhanced in I3)
				$album->auto_cover_id_max_privilege = $this->computeMaxPrivilegeCover($album, $is_nsfw_context);
				$album->auto_cover_id_least_privilege = $this->computeLeastPrivilegeCover($album, $is_nsfw_context);

				// Save without dispatching events to avoid infinite loop
				/** @disregard */
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
	 * @return array{min:Carbon|null,max:Carbon|null}
	 */
	private function computeTakenAtRange(Album $album): array
	{
		// Note:
		//  1. The order of JOINS is important.
		//     Although `JOIN` is cumulative, i.e.
		//     `photos JOIN albums` and `albums JOIN photos`
		//     should be identical, it is not with respect to the
		//     MySQL query optimizer.
		//     For an efficient query it is paramount, that the
		//     query first filters out all child albums and then
		//     selects the most/least recent photo within the child
		//     albums.
		//     If the JOIN starts with photos, MySQL first selects
		//     all photos of the entire gallery.
		//  2. The query must use the aggregation functions
		//     `MIN`/`MAX`, we must not use `ORDER BY ... LIMIT 1`.
		//     Otherwise, the MySQL optimizer first selects the
		//     photos and then joins with albums (i.e. the same
		//     effect as above).
		//     The background is rather difficult to explain, but is
		//     due to MySQL's "Limit Query Optimization"
		//     (https://dev.mysql.com/doc/refman/8.0/en/limit-optimization.html).
		//     Basically, if MySQL sees an `ORDER BY ... LIMIT ...`
		//     construction and has an applicable index for that,
		//     MySQL's built-in heuristic chooses that index with high
		//     priority and does not consider any alternatives.
		//     In this specific case, this heuristic fails splendidly.
		//
		// Further note, that PostgreSQL's optimizer is not affected
		// by any of these tricks.
		// The optimized query plan for PostgreSQL is always the same.
		// Good PosgreSQL :-)
		//
		// We must not use `Album::query->` to start the query, but
		// use a non-Eloquent query here to avoid an infinite loop
		// with this query builder.
		$result = DB::table('albums', 'a')
			->join(PA::PHOTO_ALBUM, 'a.id', '=', PA::ALBUM_ID)
			->join('photos', PA::PHOTO_ID, '=', 'photos.id')
			->where('a._lft', '>=', $album->_lft)
			->where('a._rgt', '<=', $album->_rgt)
			->whereNotNull('photos.taken_at')
			->selectRaw('MIN(photos.taken_at) as min_taken_at, MAX(photos.taken_at) as max_taken_at')
			->first();

		return [
			'min' => $result?->min_taken_at ? new Carbon($result->min_taken_at) : null,
			'max' => $result?->max_taken_at ? new Carbon($result->max_taken_at) : null,
		];
	}

	/**
	 * Compute the photo id given a user and NSFW context for an album.
	 *
	 * @param Album $album
	 * @param null|User $user
	 * @param bool $is_nsfw_context
	 * @return null|string
	 */
	private function getPhotoIdForUser(Album $album, ?User $user, bool $is_nsfw_context): ?string
	{
		$photo_query_policy = resolve(PhotoQueryPolicy::class);
		$sorting = $album->getEffectiveAlbumSorting();
		$result = $photo_query_policy
			->applySearchabilityFilter(
				query: Photo::query(),
				user: $user,
				unlocked_album_ids: [],
				origin: $album,
				include_nsfw: $is_nsfw_context)
			->orderByDesc('photos.is_starred')
			->orderBy($sorting->column->value, $sorting->order->value)
			->select('photos.id')
			->first();

		return $result?->id;
	}

	/**
	 * Compute max-privilege cover (admin/owner view).
	 *
	 * Selects best photo from album + descendants with NO access filters.
	 * Applies NSFW context: if album/parent is NSFW, allow NSFW photos; else exclude.
	 * Ordering: is_starred DESC, then taken_at DESC, then id ASC.
	 *
	 * @param Album $album
	 * @param bool  $is_nsfw_context
	 *
	 * @return string|null
	 */
	private function computeMaxPrivilegeCover(Album $album, bool $is_nsfw_context): ?string
	{
		$admin_user = User::query()->where('is_admin', '=', true)->first();
		return $this->getPhotoIdForUser($album, $admin_user, $is_nsfw_context);
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
	 * @param bool  $is_nsfw_context
	 *
	 * @return string|null
	 */
	private function computeLeastPrivilegeCover(Album $album, bool $is_nsfw_context): ?string
	{

		// First figure out who can access this folder.
		// Then apply those access rules to the photo selection.
		//
		// If the album is public VISIBLE, we only want public photos
		// => $user = null
		//
		// If the album is public INVISIBLE, this means that the public user will not see the cover
		// photo either, so we need to check if there are any users who can see the album.
		//
		// If there are no such users, the cover photo is null.
		// return null
		//
		// If there is only a single user who can see the album, we want to find photos
		// that are visible to that user. (kind of an edge case -- but possible)
		// => $user = that user
		//
		// If there are more than a single user who can see this album,
		// We consider this album as public, and look for photos Inside
		// => $user = null

		// ->toBase() to avoid casting to AccessPermission models.
		$permissions = AccessPermission::query()->where(APC::BASE_ALBUM_ID, '=', $album->id)->toBase()->get();
		if ($permissions->isEmpty()) {
			// No users can access this album, does not matters.
			return null;
		}

		if ($permissions->some(fn (AccessPermission $perm) => $perm->user_id === null && $perm->user_group_id === null && $perm->is_link_required === false)) {
			// Album is public visible
			return $this->getPhotoIdForUser($album, null, $is_nsfw_context);
		}

		// Album is not public visible
		// Find out who can access this album
		if ($permissions->count() === 1 && $permissions->first()->user_id !== null) {
			// Single user can access this album
			$user = User::query()->find($permissions->first()->user_id);
			return $this->getPhotoIdForUser($album, $user, $is_nsfw_context);
		}

		// Album is not public visible and multiple permissions exist => Consider it publically accessible
		return $this->getPhotoIdForUser($album, null, $is_nsfw_context);
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
