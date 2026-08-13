<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Tag;

use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Tags\TagWithPhotosAndAlbumsResource;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use App\Services\Cache\CacheKeyProvider;
use App\Services\Cache\ManagedCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * GetTagWithPhotosAndAlbums retrieves a tag along with its associated photos and
 * accessible albums (Feature 050 - Album Tags).
 *
 * Note that if this actions is called from a non admin user,
 * the photos and albums returned will be limited to those accessible by
 * the user.
 * This is to ensure that users only see their own photos and albums
 * associated with the tag, maintaining privacy and security.
 */
class GetTagWithPhotosAndAlbums
{
	private bool $is_cache_enabled;

	public function __construct(
		private PhotoQueryPolicy $photo_query_policy,
		private AlbumQueryPolicy $album_query_policy,
		protected readonly ConfigManager $config_manager,
		protected readonly ManagedCacheService $managed_cache_service,
		protected readonly CacheKeyProvider $cache_key_provider,
	) {
		$this->is_cache_enabled = $this->config_manager->getValueAsBool('managed_cache_albums_enabled');
	}

	/**
	 * Returns a tag with its associated photos and accessible albums.
	 *
	 * @return TagWithPhotosAndAlbumsResource
	 */
	public function do(Tag $tag): TagWithPhotosAndAlbumsResource
	{
		/** @var User $user */
		$user = Auth::user();

		return new TagWithPhotosAndAlbumsResource(
			id: $tag->id,
			name: $tag->name,
			photos: $this->getAccessiblePhotos($tag, $user),
			albums: $this->getAccessibleAlbums($tag, $user),
		);
	}

	/**
	 * Returns the photos carrying the given tag which are accessible by the
	 * current user.
	 *
	 * @return Collection<int,PhotoResource>
	 */
	private function getAccessiblePhotos(Tag $tag, User $user): Collection
	{
		$base_query = Photo::query()
			->with(['size_variants', 'statistics', 'palette', 'tags', 'rating'])
			->when(
				$user->may_administrate === false,
				fn ($q) => $q->where('photos.owner_id', Auth::id())
			)
			->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id));

		$photos_query = $this->photo_query_policy->applySensitivityFilter(
			query: $base_query,
			user: $user,
			origin: null,
			include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_tag_listing')
		);

		/** @var Collection<int,Photo> $photos */
		$photos = $photos_query->get();

		return $photos->map(fn ($photo) => new PhotoResource(
			photo: $photo,
			album_id: null,
			should_downgrade_size_variants: $user->may_administrate !== true && $user->id !== $photo->owner_id
		));
	}

	/**
	 * Returns the albums carrying the given tag which are accessible by the
	 * current user (mirrors {@link \App\Actions\Search\AlbumSearch::queryAlbums()}'s
	 * browsability filter).
	 *
	 * @return Collection<int,ThumbAlbumResource>
	 */
	private function getAccessibleAlbums(Tag $tag, User $user): Collection
	{
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();
		$user_id = Auth::id();
		// `applyBrowsabilityFilter()` is session-scoped (depends on
		// `getUnlockedAlbumIDs()`, not just the user), unlike the other five
		// cached queries in this feature — a session that unlocks a
		// password-protected album must get a fresh key, not a stale one
		// from before the unlock (NFR-053-07).
		//
		// We do not need a cryptographically secure hash here,
		// just a fast one that is unlikely to collide.
		$unlocked_hash = hash('xxh3', implode(',', $unlocked_album_ids));
		$key = $this->cache_key_provider->tagAlbumsKey($tag->id, $user_id, $unlocked_hash);

		$albums = $this->managed_cache_service->rememberIf(
			$this->is_cache_enabled,
			$key,
			[
				$this->cache_key_provider->albumTagTag($tag->id),
				$this->cache_key_provider->userTag($user_id),
				$this->cache_key_provider->albumListingGlobalTag(),
			],
			fn () => $this->queryAccessibleAlbums($tag, $user, $unlocked_album_ids),
			fn (\Illuminate\Database\Eloquent\Collection $albums): array => $this->cache_key_provider->albumTags(
				$albums->map(fn (Album $album) => $album->id)->all()
			),
		);

		return $albums->map(fn (Album $album) => ThumbAlbumResource::fromModel($album));
	}

	/**
	 * @param array<int,string> $unlocked_album_ids
	 *
	 * @return \Illuminate\Database\Eloquent\Collection<int,Album>
	 */
	private function queryAccessibleAlbums(Tag $tag, User $user, array $unlocked_album_ids): \Illuminate\Database\Eloquent\Collection
	{
		$album_query = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id));

		$this->album_query_policy->applyBrowsabilityFilter($album_query, $user, $unlocked_album_ids);

		return $album_query->get();
	}
}
