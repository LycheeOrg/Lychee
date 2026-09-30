<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Constants\AccessPermissionConstants as APC;
use App\Constants\PhotoAlbum as PA;
use App\DTO\AlbumSortingCriterion;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Events\AlbumComputedDataUpdated;
use App\Jobs\Traits\DebouncesLatestJobTrait;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\AlbumUserThumb;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Models\User;
use App\Policies\PhotoQueryPolicy;
use App\Services\AlbumBucketComputer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recomputes all computed fields for a single album and propagates to parent.
 *
 * Computed fields:
 * - max_taken_at, min_taken_at
 * - num_children, num_photos
 * - automatic covers, ranks 1–3 per privilege level (Feature 075), stored as
 *   `is_precomputed` rows of `album_user_thumbs` (Feature 076, ADR-076-01):
 *   the max-privilege row under the owner, the least-privilege row under the
 *   single shared user or `NULL`.
 */
class RecomputeAlbumStatsJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;
	use DebouncesLatestJobTrait;

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
		public bool $propagate_to_parent = true,
	) {
		$this->registerAsLatestJob();
	}

	protected function latestJobCacheKey(): string
	{
		return 'album_stats_latest_job:' . $this->album_id;
	}

	protected function latestJobLogContext(): string
	{
		return "album {$this->album_id}";
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		Log::channel('jobs')->info("Recomputing stats for album {$this->album_id}");
		$this->forgetLatestJobMarker();

		// This is a safety check to avoid recomputing albums
		// when no admin user exists.
		if (DB::table('users')->where('may_administrate', '=', true)->count() === 0) {
			Log::channel('jobs')->critical("No admin user exists, skipping recompute for album {$this->album_id}.");

			return;
		}

		try {
			// Fetch the album.
			$album = Album::where('id', '=', $this->album_id)->first();
			if ($album === null) {
				Log::channel('jobs')->warning("Album {$this->album_id} not found, skipping recompute.");

				return;
			}

			$is_nsfw_context = DB::table('albums')
				->leftJoin('base_albums as base', 'albums.id', '=', 'base.id')
				->where('base.is_nsfw', '=', true)
				->where('albums._lft', '<=', $album->_lft)
				->where('albums._rgt', '>=', $album->_rgt)
				->count() > 0;

			// Compute counts
			$album->num_children = $this->computeNumChildren($album);
			$album->num_photos = $this->computeNumPhotos($album);

			// Compute date range
			$dates = $this->computeTakenAtRange($album);
			$album->min_taken_at = $dates['min'];
			$album->max_taken_at = $dates['max'];

			// Compute cover IDs: ranks 1–3 per privilege level (Feature 075, FR-075-02).
			$max_covers = $this->computeMaxPrivilegeCovers($album, $is_nsfw_context);
			[$least_key, $least_covers] = $this->computeLeastPrivilegeCovers($album, $is_nsfw_context);
			Log::channel('jobs')->debug("Computed covers for album {$album->id}: max_privilege=" . implode('/', array_map(fn ($id) => $id ?? 'null', $max_covers)) . ', least_privilege=' . implode('/', array_map(fn ($id) => $id ?? 'null', $least_covers)) . ', least_privilege_key=' . ($least_key ?? 'null'));

			$album->bucket_id = $this->computeBucket($album);
			DB::transaction(function () use ($album, $max_covers, $least_key, $least_covers): void {
				$album->save();
				$this->writeCoverRows($album, $max_covers, $least_key, $least_covers);
			});

			AlbumComputedDataUpdated::dispatch($album->id);

			// Propagate to parent if exists
			if ($album->parent_id !== null && $this->propagate_to_parent) {
				Log::channel('jobs')->debug("Propagating to parent {$album->parent_id}");
				self::dispatch($album->parent_id);
			}
		} catch (\Exception $e) {
			Log::channel('jobs')->error("Propagation stopped at album {$this->album_id} due to failure: " . $e->getMessage());

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
	 * Number of automatic cover ranks stored per privilege level
	 * (Feature 075, FR-075-02): the cover plus two side covers.
	 */
	public const COVER_RANKS = 3;

	/**
	 * Compute the photo ids of ranks 1–3 given a user and NSFW context for
	 * an album, padded with `null` when fewer photos qualify.
	 *
	 * @param Album     $album
	 * @param User|null $user
	 * @param bool      $is_nsfw_context
	 *
	 * @return array{0:string|null,1:string|null,2:string|null}
	 */
	private function getPhotoIdsForUser(Album $album, ?User $user, bool $is_nsfw_context): array
	{
		$photo_query_policy = resolve(PhotoQueryPolicy::class);
		$sorting = $album->getEffectivePhotoSorting();

		$query = $photo_query_policy
			->applySearchabilityFilter(
				query: Photo::query(),
				user: $user,
				unlocked_album_ids: [],
				origin: $album,
				include_nsfw: $is_nsfw_context);

		(new SortingDecorator($query))
			->orderPhotosBy(ColumnSortingType::IS_HIGHLIGHTED, OrderSortingType::DESC)
			->orderPhotosBy($sorting->column, $sorting->order)
			->applyOrdering();

		/** @var list<string> $ids */
		$ids = $query->select('photos.id')->limit(self::COVER_RANKS)->toBase()->pluck('id')->all();

		return [$ids[0] ?? null, $ids[1] ?? null, $ids[2] ?? null];
	}

	/**
	 * Replace the album's precomputed cover rows (Feature 076, FR-076-02):
	 * one delete, then one insert of at most two rows. The owner row carries
	 * the max-privilege ranks, the least-privilege row is keyed per
	 * {@see self::computeLeastPrivilegeCovers()}. A rank-1-less triple, or a
	 * least-privilege key equal to the owner (the owner row already serves
	 * them), writes no row.
	 *
	 * Delete + insert rather than an upsert: the unique key includes the
	 * generated `user_id_unique_key`, which MySQL/MariaDB refuse to receive a
	 * value for, and stale least-privilege rows (a changed key) must go anyway.
	 *
	 * @param array{0:string|null,1:string|null,2:string|null} $max_covers
	 * @param array{0:string|null,1:string|null,2:string|null} $least_covers
	 */
	private function writeCoverRows(Album $album, array $max_covers, ?int $least_key, array $least_covers): void
	{
		AlbumUserThumb::query()->where('album_id', '=', $album->id)->where('is_precomputed', '=', true)->delete();

		$rows = array_values(array_filter([
			self::coverRow($album->id, $album->owner_id, $max_covers),
			$least_key === $album->owner_id ? null : self::coverRow($album->id, $least_key, $least_covers),
		], fn (?array $row): bool => $row !== null));

		if (count($rows) > 0) {
			AlbumUserThumb::query()->insert($rows);
		}
	}

	/**
	 * @param array{0:string|null,1:string|null,2:string|null} $covers
	 *
	 * @return array{album_id:string,user_id:int|null,photo_id:string,photo_id_2:string|null,photo_id_3:string|null,is_precomputed:bool}|null
	 */
	private static function coverRow(string $album_id, ?int $user_id, array $covers): ?array
	{
		if ($covers[0] === null) {
			return null;
		}

		return [
			'album_id' => $album_id,
			'user_id' => $user_id,
			'photo_id' => $covers[0],
			'photo_id_2' => $covers[1],
			'photo_id_3' => $covers[2],
			'is_precomputed' => true,
		];
	}

	/**
	 * Compute max-privilege cover (admin/owner view).
	 *
	 * Selects best photo from album + descendants with NO access filters.
	 * Applies NSFW context: if album/parent is NSFW, allow NSFW photos; else exclude.
	 * Ordering: is_highlighted DESC, then taken_at DESC, then id ASC.
	 *
	 * @param Album $album
	 * @param bool  $is_nsfw_context
	 *
	 * @return array{0:string|null,1:string|null,2:string|null} ranks 1–3
	 */
	private function computeMaxPrivilegeCovers(Album $album, bool $is_nsfw_context): array
	{
		$admin_user = User::query()->where('may_administrate', '=', true)->first();

		return $this->getPhotoIdsForUser($album, $admin_user, $is_nsfw_context);
	}

	/**
	 * Compute least-privilege cover (public view).
	 *
	 * Selects best photo from album + descendants WITH access control filters.
	 * Only includes photos visible to all users (public photos).
	 * Applies NSFW context: if album/parent is NSFW, allow NSFW photos; else exclude.
	 * Ordering: is_highlighted DESC, then taken_at DESC, then id ASC.
	 *
	 * Also returns the `album_user_thumbs.user_id` the result is stored under
	 * (Feature 076, ADR-076-01): the single user the album is shared with, or
	 * `NULL` for the public view.
	 *
	 * @param Album $album
	 * @param bool  $is_nsfw_context
	 *
	 * @return array{0:int|null,1:array{0:string|null,1:string|null,2:string|null}} the row key and ranks 1–3
	 */
	private function computeLeastPrivilegeCovers(Album $album, bool $is_nsfw_context): array
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
			return [null, [null, null, null]];
		}

		// Album is not public visible
		// Find out who can access this album
		if ($permissions->count() === 1 && $permissions->first()->user_id !== null) {
			// Single user can access this album
			$user_id = (int) $permissions->first()->user_id;
			$user = User::query()->find($user_id);

			return [$user_id, $this->getPhotoIdsForUser($album, $user, $is_nsfw_context)];
		}

		// Album is not public visible and multiple permissions exist => Consider it publically accessible
		return [null, $this->getPhotoIdsForUser($album, null, $is_nsfw_context)];
	}

	/**
	 * Compute `bucket_id`: resolves the album's own *parent's* effective
	 * sort column (or the instance-wide default for a root album) and granularity,
	 * then delegates the actual truncation to {@see AlbumBucketComputer} —
	 * shared with {@see RecomputeChildAlbumBucketsJob} and the backfill command,
	 * so the truncation logic itself lives in exactly one place.
	 * Never queries `photos` — every value is derivable from the album row
	 * itself plus its parent's already-loaded settings.
	 *
	 * @param Album $album
	 *
	 * @return string|null
	 */
	private function computeBucket(Album $album): ?string
	{
		$sorting_column = $album->parent?->getEffectiveAlbumSorting()->column
			?? AlbumSortingCriterion::createDefault()->column;

		$bucket_computer = resolve(AlbumBucketComputer::class);
		$granularity = $bucket_computer->resolveGranularity($album->parent?->album_timeline);

		return $bucket_computer->compute(
			sorting_column: $sorting_column,
			granularity: $granularity,
			title: $album->title,
			title_base: $album->title_base,
			created_at: $album->created_at,
			min_taken_at: $album->min_taken_at,
			max_taken_at: $album->max_taken_at,
		);
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
		Log::channel('jobs')->error("Job failed permanently for album {$this->album_id}: " . $exception->getMessage());
		// Do NOT dispatch parent job on failure - propagation stops here
	}
}
