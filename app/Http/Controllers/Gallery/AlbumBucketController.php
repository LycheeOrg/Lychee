<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TitleBucketMode;
use App\Http\Requests\Album\GetAlbumBucketsRequest;
use App\Http\Resources\V3\AlbumBucketResource;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Services\AlbumBucketComputer;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves `GET /api/v3/Albums/{album_id}/children/buckets` (Feature 061,
 * API-061-01): a cheap, index-served `GROUP BY bucket_id` count of one
 * parent album's direct children, plus a ready-to-render display label per
 * bucket — lets a client render sticky date headers and size a virtual-scroll
 * container before fetching a single child row.
 */
class AlbumBucketController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumBucketComputer $bucket_computer,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	public function index(GetAlbumBucketsRequest $request): AlbumBucketResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		$key = $this->cache_key_provider->albumBucketsKey($album->id, $user?->id);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[$this->cache_key_provider->albumChildrenTag($album->id)],
			fn (): AlbumBucketResource => $this->queryBuckets($album, $user),
			ttl: $ttl,
		);
	}

	private function queryBuckets(Album $album, ?User $user): AlbumBucketResource
	{
		$sorting = $album->getEffectiveAlbumSorting();

		// OWNER_ID is a valid effective sort column, but every direct child
		// of a given album always shares that album's exact owner_id -> it
		// can never produce more than one bucket (FR-061-06). Short-circuit
		// without ever running a GROUP BY.
		if ($sorting->column === ColumnSortingType::OWNER_ID) {
			return new AlbumBucketResource(bucket_ids: [], counts: [], labels: [], bucketable: false);
		}

		$query = Album::query()->where('parent_id', '=', $album->id);
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';

		$rows = $query
			->select(['albums.bucket_id'])
			->selectRaw('COUNT(*) as bucket_count')
			->groupBy('albums.bucket_id')
			// NULL ("unknown") always sorts last, regardless of $direction.
			->orderByRaw('(albums.bucket_id IS NULL) ASC')
			->orderBy('albums.bucket_id', $direction)
			->toBase()
			->get();

		$bucket_ids = [];
		$counts = [];
		foreach ($rows as $row) {
			$bucket_ids[] = $row->bucket_id ?? 'unknown';
			$counts[] = (int) $row->bucket_count;
		}

		$labels = $this->computeLabels($bucket_ids, $sorting->column, $album->album_timeline);

		return new AlbumBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $labels, bucketable: true);
	}

	/**
	 * Computes one display label per distinct bucket (FR-061-18) — bounded
	 * by bucket count, never by child-row count (NFR-061-01's scope
	 * explicitly exempts this: it runs entirely in PHP, after the
	 * `GROUP BY`).
	 *
	 * @param string[] $bucket_ids
	 */
	private function computeLabels(array $bucket_ids, ColumnSortingType $sorting_column, ?TimelineAlbumGranularity $album_timeline): array
	{
		$is_alphabetical_title = $sorting_column === ColumnSortingType::TITLE &&
			(request()->configs()->getValueAsEnum('title_bucket_mode', TitleBucketMode::class) ?? TitleBucketMode::DATE_PREFIX) === TitleBucketMode::ALPHABETICAL;

		if ($is_alphabetical_title) {
			// Already human-readable; never Carbon-parsed.
			return $bucket_ids;
		}

		$granularity = $this->bucket_computer->resolveGranularity($album_timeline);
		$format = match ($granularity) {
			TimelineAlbumGranularity::YEAR => request()->configs()->getValueAsString('timeline_album_date_format_year'),
			TimelineAlbumGranularity::MONTH => request()->configs()->getValueAsString('timeline_album_date_format_month'),
			TimelineAlbumGranularity::DAY => request()->configs()->getValueAsString('timeline_album_date_format_day'),
			default => request()->configs()->getValueAsString('timeline_album_date_format_year'),
		};

		return array_map(
			fn (string $bucket_id): string => $bucket_id === 'unknown' ? 'unknown' : Carbon::parse($bucket_id)->format($format),
			$bucket_ids,
		);
	}
}
