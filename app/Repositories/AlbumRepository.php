<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Repositories;

use App\Constants\PersonAlbumPersons;
use App\DTO\AlbumSortingCriterion;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use App\Services\PersonAlbumMatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Repository for Album queries.
 *
 * Centralizes album query logic including eager loading and sorting.
 */
class AlbumRepository
{
	public function __construct(
		protected AlbumQueryPolicy $album_query_policy,
		protected ManagedCacheService $managed_cache_service,
		protected CacheKeyProvider $cache_key_provider,
		protected ConfigManager $config_manager,
		protected PersonAlbumMatcher $person_album_matcher,
	) {
	}

	/**
	 * Get paginated child albums with all necessary relations eager-loaded.
	 *
	 * @param string|null           $album_id the parent album ID (null for root albums)
	 * @param AlbumSortingCriterion $sorting  the sorting criteria
	 * @param int                   $per_page number of albums per page
	 *
	 * @return LengthAwarePaginator<Album>
	 *
	 * @throws \App\Exceptions\Internal\InvalidOrderDirectionException
	 * @throws \App\Contracts\Exceptions\InternalLycheeException
	 */
	public function getChildrenPaginated(
		?string $album_id,
		AlbumSortingCriterion $sorting,
		int $per_page,
	): LengthAwarePaginator {
		$user_id = Auth::id();
		$page = Paginator::resolveCurrentPage();

		$key = $this->cache_key_provider->albumChildrenPageKey($album_id, $user_id, $page, $per_page, $sorting);
		$tags = [
			$this->cache_key_provider->albumChildrenTag($album_id),
			$this->cache_key_provider->userTag($user_id),
			$this->cache_key_provider->albumListingGlobalTag(),
		];

		/** @var LengthAwarePaginator<Album> $result */
		$result = $this->managed_cache_service->rememberIf(
			$this->config_manager->getValueAsBool('managed_cache_albums_enabled'),
			$key,
			$tags,
			fn (): LengthAwarePaginator => $this->queryChildrenPaginated($album_id, $sorting, $per_page),
			fn (LengthAwarePaginator $result): array => $this->cache_key_provider->albumTags(
				array_map(fn (Album $album) => $album->id, $result->items())
			),
		);

		return $result;
	}

	/**
	 * @return LengthAwarePaginator<Album>
	 */
	private function queryChildrenPaginated(?string $album_id, AlbumSortingCriterion $sorting, int $per_page): LengthAwarePaginator
	{
		$query = Album::query()
			->with(['owner'])
			->without(['thumb']) // Yes we do NOT want them yet.
			->where('parent_id', '=', $album_id);

		// Apply visibility filter
		/** @var ?User $user */
		$user = Auth::user();
		$query = $this->album_query_policy->applyVisibilityFilter($query, $user);

		// Apply sorting via SortingDecorator
		/** @var SortingDecorator<Album> */
		$sorting_decorator = new SortingDecorator($query);

		return $sorting_decorator
			->orderBy($sorting->column, $sorting->order)
			->paginate($per_page);
	}

	/**
	 * Get the real Albums carrying (as their own {@see \App\Models\Album::tags()}
	 * metadata, Feature 050) every tag referenced by the given TagAlbum's
	 * criteria (`is_and` ⇒ all of them, otherwise any of them).
	 *
	 * @return LengthAwarePaginator<Album>
	 *
	 * @throws \App\Exceptions\Internal\InvalidOrderDirectionException
	 * @throws \App\Contracts\Exceptions\InternalLycheeException
	 */
	public function getMatchingAlbumsForTagPaginated(TagAlbum $tag_album, int $per_page): LengthAwarePaginator
	{
		if (!$this->config_manager->getValueAsBool('TA_albums_listing_enabled')) {
			return new LengthAwarePaginator([], 0, $per_page);
		}

		$user_id = Auth::id();
		$page = Paginator::resolveCurrentPage();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$unlocked_digest = hash('xxh3', implode(',', $unlocked_album_ids));
		$tag_ids = $tag_album->tags->pluck('id')->all();

		$key = $this->cache_key_provider->tagAlbumMatchingAlbumsPageKey($tag_album->id, $user_id, $page, $per_page, $unlocked_digest);
		$tags = [
			$this->cache_key_provider->albumTag($tag_album->id),
			...$this->cache_key_provider->albumTagTags($tag_ids),
			$this->cache_key_provider->userTag($user_id),
			$this->cache_key_provider->albumListingGlobalTag(),
		];

		/** @var LengthAwarePaginator<Album> $result */
		$result = $this->managed_cache_service->rememberIf(
			$this->config_manager->getValueAsBool('managed_cache_albums_enabled'),
			$key,
			$tags,
			fn (): LengthAwarePaginator => $this->queryMatchingAlbumsForTagPaginated($tag_ids, $tag_album->is_and, $unlocked_album_ids, $per_page),
			fn (LengthAwarePaginator $result): array => $this->cache_key_provider->albumTags(
				array_map(fn (Album $album) => $album->id, $result->items())
			),
		);

		return $result;
	}

	/**
	 * @param int[]    $tag_ids
	 * @param string[] $unlocked_album_ids
	 *
	 * @return LengthAwarePaginator<Album>
	 */
	private function queryMatchingAlbumsForTagPaginated(array $tag_ids, bool $is_and, array $unlocked_album_ids, int $per_page): LengthAwarePaginator
	{
		$query = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id');

		if (count($tag_ids) === 0) {
			$query->whereRaw('1 = 0');
		} elseif ($is_and) {
			// Eloquent composes repeated whereHas() on the same relation as
			// AND — one constraint per required tag.
			foreach ($tag_ids as $tag_id) {
				$query->whereHas('tags', fn (Builder $q) => $q->where('tags.id', '=', $tag_id));
			}
		} else {
			$query->whereHas('tags', fn (Builder $q) => $q->whereIn('tags.id', $tag_ids));
		}

		/** @var ?User $user */
		$user = Auth::user();
		$this->album_query_policy->applyBrowsabilityFilter($query, $user, $unlocked_album_ids);

		$sorting = AlbumSortingCriterion::createDefault();
		/** @var SortingDecorator<Album> */
		$sorting_decorator = new SortingDecorator($query);

		return $sorting_decorator
			->orderBy($sorting->column, $sorting->order)
			->paginate($per_page);
	}

	/**
	 * Get the real Albums containing at least one photo matching the given
	 * PersonAlbum's face/person criteria.
	 *
	 * @return LengthAwarePaginator<Album>
	 *
	 * @throws \App\Exceptions\Internal\InvalidOrderDirectionException
	 * @throws \App\Contracts\Exceptions\InternalLycheeException
	 */
	public function getMatchingAlbumsForPersonPaginated(PersonAlbum $person_album, int $per_page): LengthAwarePaginator
	{
		if (!$this->config_manager->getValueAsBool('PA_albums_listing_enabled')) {
			return new LengthAwarePaginator([], 0, $per_page);
		}

		$user_id = Auth::id();
		$page = Paginator::resolveCurrentPage();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$unlocked_digest = hash('xxh3', implode(',', $unlocked_album_ids));

		// Every person in the album's criteria (not just those visible to the
		// current user) is used for cache tagging, so the entry is invalidated
		// conservatively regardless of which user's session it was cached under.
		$person_ids = DB::table(PersonAlbumPersons::PERSON_ALBUM_PERSONS)
			->where(PersonAlbumPersons::ALBUM_ID, '=', $person_album->id)
			->pluck('person_id')
			->all();

		$key = $this->cache_key_provider->personAlbumMatchingAlbumsPageKey($person_album->id, $user_id, $page, $per_page, $unlocked_digest);
		$tags = [
			$this->cache_key_provider->albumTag($person_album->id),
			...$this->cache_key_provider->albumPersonTags($person_ids),
			$this->cache_key_provider->userTag($user_id),
			$this->cache_key_provider->albumListingGlobalTag(),
		];

		/** @var LengthAwarePaginator<Album> $result */
		$result = $this->managed_cache_service->rememberIf(
			$this->config_manager->getValueAsBool('managed_cache_albums_enabled'),
			$key,
			$tags,
			fn (): LengthAwarePaginator => $this->queryMatchingAlbumsForPersonPaginated($person_album, $unlocked_album_ids, $per_page),
			fn (LengthAwarePaginator $result): array => $this->cache_key_provider->albumTags(
				array_map(fn (Album $album) => $album->id, $result->items())
			),
		);

		return $result;
	}

	/**
	 * @param string[] $unlocked_album_ids
	 *
	 * @return LengthAwarePaginator<Album>
	 */
	private function queryMatchingAlbumsForPersonPaginated(PersonAlbum $person_album, array $unlocked_album_ids, int $per_page): LengthAwarePaginator
	{
		/** @var ?User $user */
		$user = Auth::user();
		$matching_photo_ids = $this->person_album_matcher->buildMatchingPhotoIdsQuery($person_album, $user, $unlocked_album_ids);

		$query = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->whereExists(fn ($q) => $q
				->select(DB::raw(1))
				->from('photo_album')
				->whereColumn('photo_album.album_id', '=', 'albums.id')
				->whereIn('photo_album.photo_id', $matching_photo_ids));

		$this->album_query_policy->applyBrowsabilityFilter($query, $user, $unlocked_album_ids);

		$sorting = AlbumSortingCriterion::createDefault();
		/** @var SortingDecorator<Album> */
		$sorting_decorator = new SortingDecorator($query);

		return $sorting_decorator
			->orderBy($sorting->column, $sorting->order)
			->paginate($per_page);
	}
}
