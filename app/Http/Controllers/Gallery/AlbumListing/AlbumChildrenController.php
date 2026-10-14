<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Actions\Album\StructOfArrays\QueryChildrenForAlbum;
use App\Actions\Album\StructOfArrays\QueryChildrenForPerson;
use App\Actions\Album\StructOfArrays\QueryChildrenForTag;
use App\Actions\Album\StructOfArrays\QueryRightsForAlbum;
use App\Actions\Album\StructOfArrays\QueryRightsForMatchingAlbums;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\ColumnSortingType;
use App\Http\Requests\Album\GetAlbumBucketsRequest;
use App\Http\Requests\Album\GetAlbumChildrenDataRequest;
use App\Http\Requests\Album\GetAlbumChildrenRightsRequest;
use App\Http\Resources\V3\AlbumBucketResource;
use App\Http\Resources\V3\AlbumDataResource;
use App\Http\Resources\V3\AlbumRightsResource;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\AlbumRepository;
use App\Repositories\ConfigManager;
use App\Services\AlbumBucketComputer;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Serves the sub-album tier: `GET /api/v3/Albums/{album_id}`,
 * `/Albums/{album_id}/buckets`, `/Albums/{album_id}/rights` — the `/children`
 * path segment is dropped, mirroring root's own naming; the query logic and
 * response shape below are an unmodified, verbatim port of the former
 * `AlbumBucketController`/`AlbumChildrenDataController`/`AlbumChildrenRightsController`,
 * merged into one class per method — only the class/namespace boundary and
 * the route path changed.
 */
class AlbumChildrenController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected AlbumBucketComputer $bucket_computer,
		protected AlbumRepository $album_repository,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
		protected ConfigManager $config_manager,
		protected QueryChildrenForAlbum $query_children_for_album,
		protected QueryChildrenForTag $query_children_for_tag,
		protected QueryChildrenForPerson $query_children_for_person,
		protected QueryRightsForAlbum $query_rights_for_album,
		protected QueryRightsForMatchingAlbums $query_rights_for_matching_albums,
	) {
	}

	// ── GET /Albums/{album_id}/buckets ─────────────────────────────

	public function buckets(GetAlbumBucketsRequest $request): AlbumBucketResource
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
			[
				$this->cache_key_provider->albumChildrenTag($album->id),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): AlbumBucketResource => $this->queryBuckets($album, $user),
			ttl: $ttl,
		);
	}

	private function queryBuckets(Album $album, ?User $user): AlbumBucketResource
	{
		$sorting = $album->getEffectiveAlbumSorting();

		// OWNER_ID is a valid effective sort column, but every direct child
		// of a given album always shares that album's exact owner_id -> it
		// can never produce more than one bucket. Short-circuit
		// without ever running a GROUP BY.
		if ($sorting->column === ColumnSortingType::OWNER_ID) {
			return new AlbumBucketResource(bucket_ids: [], counts: [], labels: [], bucketable: false);
		}

		$scheme = $this->bucket_computer->scheme($sorting->column, $album->album_timeline);
		$id_sql = $scheme->storedIdSql('albums.bucket_key');

		$query = Album::query()->where('parent_id', '=', $album->id);
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);
		$query->select(DB::raw($id_sql . ' as bucket_id'))
			->selectRaw('COUNT(*) as bucket_count')
			->groupByRaw($id_sql);
		$scheme->orderRows($query, $id_sql, $sorting->order);

		[$bucket_ids, $counts] = $scheme->collectBuckets($query->toBase()->get());

		return new AlbumBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $scheme->labels($bucket_ids), bucketable: true);
	}

	// ── GET /Albums/{album_id} ──────────────────────────────────────

	public function index(GetAlbumChildrenDataRequest $request): AlbumDataResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		// AlbumPolicy::getUnlockedAlbumIDs() (session-scoped) affects this
		// response two ways: for TagAlbum/PersonAlbum it curates which
		// albums match at all (mirrors the pre-existing
		// AlbumRepository::getMatchingAlbumsForTagPaginated()/...ForPersonPaginated()
		// convention); for every album type, a locked child's `cover_ids`
		// entry (#4704) depends on it too. Either way the cache key must
		// vary with it, so it is always computed, never conditionally
		// skipped for a regular Album.
		$unlocked_digest = $this->cache_key_provider->unlockedAlbumsDigest();

		$key = $this->cache_key_provider->albumChildrenDataKey($album->get_id(), $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag($album->get_id()),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): AlbumDataResource => $this->queryChildren($album, $user),
			ttl: $ttl,
		);
	}

	private function queryChildren(AbstractAlbum $album, ?User $user): AlbumDataResource
	{
		if ($album instanceof Album) {
			return $this->query_children_for_album->do($album, $user);
		}

		if ($album instanceof TagAlbum) {
			return $this->query_children_for_tag->do($album, $user);
		}

		/** @var PersonAlbum $album */
		return $this->query_children_for_person->do($album, $user);
	}

	// ── GET /Albums/{album_id}/rights ───────────────────────────────

	public function rights(GetAlbumChildrenRightsRequest $request): AlbumRightsResource
	{
		$album = $request->album();
		/** @var User|null $user */
		$user = Auth::user();

		// See index() for why the unlocked-album digest must be part of this
		// key for TagAlbum/PersonAlbum.
		$unlocked_digest = ($album instanceof TagAlbum || $album instanceof PersonAlbum)
			? $this->cache_key_provider->unlockedAlbumsDigest()
			: '';

		$key = $this->cache_key_provider->albumChildrenRightsKey($album->get_id(), $user?->id, $unlocked_digest);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->albumChildrenTag($album->get_id()),
				$this->cache_key_provider->userTag($user?->id),
			],
			fn (): AlbumRightsResource => $this->queryRights($album, $user),
			ttl: $ttl,
		);
	}

	private function queryRights(AbstractAlbum $album, ?User $user): AlbumRightsResource
	{
		if ($album instanceof Album) {
			return $this->query_rights_for_album->do($album, $user);
		}

		if ($album instanceof TagAlbum) {
			if (!$this->config_manager->getValueAsBool('TA_albums_listing_enabled')) {
				return $this->query_rights_for_matching_albums->emptyResource($album);
			}

			$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
			$tag_ids = $album->tags->pluck('id')->all();
			$query = $this->album_repository->queryMatchingAlbumsForTag($tag_ids, $album->is_and, $unlocked_album_ids);

			return $this->query_rights_for_matching_albums->do($album, $query, $user);
		}

		// PersonAlbum — the only remaining type GetAlbumChildrenRightsRequest resolves.
		if (!$this->config_manager->getValueAsBool('PA_albums_listing_enabled')) {
			return $this->query_rights_for_matching_albums->emptyResource($album);
		}

		/** @var PersonAlbum $album */
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$query = $this->album_repository->queryMatchingAlbumsForPerson($album, $unlocked_album_ids);

		return $this->query_rights_for_matching_albums->do($album, $query, $user);
	}
}
