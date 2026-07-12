<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Tag;

use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Http\Resources\Tags\TagWithPhotosResource;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * GetTagWithPhotos retrieves a tag along with its associated photos and
 * accessible albums (Feature 050 - Album Tags).
 *
 * Note that if this actions is called from a non admin user,
 * the photos and albums returned will be limited to those accessible by
 * the user.
 * This is to ensure that users only see their own photos and albums
 * associated with the tag, maintaining privacy and security.
 */
class GetTagWithPhotos
{
	public function __construct(
		private PhotoQueryPolicy $photo_query_policy,
		private AlbumQueryPolicy $album_query_policy,
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Returns a tag with its associated photos and accessible albums.
	 *
	 * @return TagWithPhotosResource
	 */
	public function do(Tag $tag): TagWithPhotosResource
	{
		/** @var User $user */
		$user = Auth::user();

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
		$photo_resources = $photos->map(fn ($photo) => new PhotoResource(
			photo: $photo,
			album_id: null,
			should_downgrade_size_variants: $user->may_administrate !== true && $user->id !== $photo->owner_id
		));

		return new TagWithPhotosResource(
			id: $tag->id,
			name: $tag->name,
			photos: $photo_resources,
			albums: $this->getAccessibleAlbums($tag, $user),
		);
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

		$album_query = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id));

		$this->album_query_policy->applyBrowsabilityFilter($album_query, $user, $unlocked_album_ids);

		$albums = $album_query->get();

		return $albums->map(fn (Album $album) => ThumbAlbumResource::fromModel($album));
	}
}
