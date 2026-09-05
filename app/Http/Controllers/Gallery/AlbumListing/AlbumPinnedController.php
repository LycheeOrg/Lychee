<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\Actions\Album\StructOfArrays\Traits\BuildsAlbumCategoryResource;
use App\Enum\AlbumListingScope;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Http\Requests\Album\GetScopedAlbumsRequest;
use App\Http\Resources\V3\AlbumCategoryResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Serves the flat, un-bucketed pinned-albums listing: `GET /Albums/pinned`
 * — takes the same `own`\|`shared` split as root, always as one flat,
 * ungrouped list — never per-owner buckets, never a `/buckets` route (NG9).
 */
class AlbumPinnedController extends Controller
{
	use BuildsAlbumCategoryResource;

	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected ConfigManager $config_manager,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	public function pinned(GetScopedAlbumsRequest $request): AlbumCategoryResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$scope = $request->scope();
		$pinned_col = $this->config_manager->getValueAsEnum('sorting_pinned_albums_col', ColumnSortingType::class);
		$pinned_order = $this->config_manager->getValueAsEnum('sorting_pinned_albums_order', OrderSortingType::class);

		$key = $this->cache_key_provider->pinnedAlbumsListingKey($user?->id, $pinned_col, $pinned_order, $scope);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->pinnedAlbumsListingTag(),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumCategoryResource => $this->queryPinned($user, $scope, $pinned_col, $pinned_order),
			ttl: $ttl,
		);
	}

	private function queryPinned(?User $user, AlbumListingScope $scope, ?ColumnSortingType $pinned_col, ?OrderSortingType $pinned_order): AlbumCategoryResource
	{
		$query = $this->album_query_policy->applyVisibilityFilter(
			Album::query()->joinSub(DB::table('base_albums')->select(['id', 'is_pinned'])->where('is_pinned', '=', true), 'pinned', 'pinned.id', '=', 'albums.id'),
			$user
		);
		$this->applyScopePredicate($query, $scope, $user);
		(new SortingDecorator($query))->orderBy($pinned_col ?? ColumnSortingType::CREATED_AT, $pinned_order ?? OrderSortingType::ASC)->applyOrdering();

		$rows = $query
			->select([
				'albums.id',
				'base_albums.title',
				'albums.cover_id',
				'albums.auto_cover_id_max_privilege',
				'albums.auto_cover_id_least_privilege',
				'base_albums.owner_id',
			])
			->toBase()
			->get();

		return $this->toCategoryResource($rows, resolve_cover: true, user: $user);
	}

	/**
	 * `own` = `owner_id = $user->id`; `shared` = `owner_id != $user->id`
	 * (unfiltered for a guest, who has no "own" albums). Always one flat,
	 * ungrouped list either way (NG9) — never a `GROUP BY owner_id`.
	 *
	 * @param Builder<Album> $query
	 */
	private function applyScopePredicate(Builder $query, AlbumListingScope $scope, ?User $user): void
	{
		if ($scope === AlbumListingScope::OWN) {
			// GetScopedAlbumsRequest guarantees $user !== null whenever OWN
			// is reachable.
			$query->where('base_albums.owner_id', '=', $user->id);
		} elseif ($user !== null) {
			$query->where('base_albums.owner_id', '!=', $user->id);
		}
	}
}
