<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery\AlbumListing;

use App\DTO\AlbumSortingCriterion;
use App\Enum\AlbumListingScope;
use App\Http\Requests\Album\GetScopedAlbumsRequest;
use App\Http\Resources\V3\AlbumCategoryResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\PersonAlbum;
use App\Models\User;
use App\Policies\AlbumQueryPolicy;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves the flat, un-bucketed person-album listing: `GET /Albums/persons`
 * — takes the same `own`\|`shared` split as root, always as one flat,
 * ungrouped list — never per-owner buckets, never a `/buckets` route (NG9).
 */
class AlbumPersonController extends Controller
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected ConfigManager $config_manager,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
	) {
	}

	public function persons(GetScopedAlbumsRequest $request): AlbumCategoryResource
	{
		/** @var User|null $user */
		$user = Auth::user();
		$scope = $request->scope();
		$sorting = AlbumSortingCriterion::createDefault();

		if (!$this->config_manager->getValueAsBool('ai_vision_face_enabled')) {
			return new AlbumCategoryResource(ids: [], titles: [], cover_ids: [], owner_ids: []);
		}

		$key = $this->cache_key_provider->personAlbumsListingKey($user?->id, $sorting, $scope);
		$enabled = $request->configs()->getValueAsBool('managed_cache_albums_enabled');
		$ttl = $request->configs()->getValueAsInt('managed_cache_ttl');

		return $this->managed_cache_service->rememberIf(
			$enabled,
			$key,
			[
				$this->cache_key_provider->personAlbumsListingTag(),
				$this->cache_key_provider->userTag($user?->id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn (): AlbumCategoryResource => $this->queryPersons($user, $scope, $sorting),
			ttl: $ttl,
		);
	}

	private function queryPersons(?User $user, AlbumListingScope $scope, AlbumSortingCriterion $sorting): AlbumCategoryResource
	{
		$query = $this->album_query_policy->applyVisibilityFilter(PersonAlbum::query(), $user);
		$this->applyScopePredicate($query, $scope, $user);
		(new SortingDecorator($query))->orderBy($sorting->column->fallbackForCategoryAlbumListing(), $sorting->order)->applyOrdering();

		$rows = $query
			->select(['person_albums.id', 'base_albums.title', 'base_albums.owner_id'])
			->toBase()
			->get();

		$ids = [];
		$titles = [];
		$cover_ids = [];
		$owner_ids = [];
		foreach ($rows as $row) {
			$ids[] = $row->id;
			$titles[] = $row->title;
			// PersonAlbum carries no cover_id column at all — resolving one
			// live would require a photos query this flat listing
			// deliberately never runs.
			$cover_ids[] = null;
			$owner_ids[] = (string) $row->owner_id;
		}

		return new AlbumCategoryResource(ids: $ids, titles: $titles, cover_ids: $cover_ids, owner_ids: $owner_ids);
	}

	/**
	 * `own` = `owner_id = $user->id`; `shared` = `owner_id != $user->id`
	 * (unfiltered for a guest, who has no "own" albums). Always one flat,
	 * ungrouped list either way (NG9) — never a `GROUP BY owner_id`.
	 *
	 * @param Builder<PersonAlbum> $query
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
