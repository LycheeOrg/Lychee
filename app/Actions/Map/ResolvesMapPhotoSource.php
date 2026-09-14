<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Map;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\MapViewport;
use App\Eloquent\FixedQueryBuilder;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Resolves the base candidate photo query for the Map's root (cross-library)
 * and album (± sub-albums) scopes — the exact same `Photo` rows
 * {@see \App\Actions\Albums\PositionData::do()}/{@see \App\Actions\Album\PositionData::get()}
 * resolve today, minus their eager loads and `->get()` (FR-067-03, FR-067-04).
 * Shared by {@see QueryMapBuckets} and {@see QueryMapPhotos}.
 */
trait ResolvesMapPhotoSource
{
	/**
	 * Reproduces {@see \App\Actions\Albums\PositionData::do()}'s exact
	 * filter — `PhotoQueryPolicy::applySearchabilityFilter()`,
	 * `origin: null`, `hide_nsfw_in_map`-driven `include_nsfw` — minus the
	 * eager loads and `->get()`.
	 *
	 * @return FixedQueryBuilder<Photo>
	 */
	private function resolveRootQuery(?User $user): FixedQueryBuilder
	{
		/** @var PhotoQueryPolicy $photo_query_policy */
		$photo_query_policy = resolve(PhotoQueryPolicy::class);
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		/** @var FixedQueryBuilder<Photo> $query */
		$query = $photo_query_policy->applySearchabilityFilter(
			query: Photo::query()
				->whereNotNull('latitude')
				->whereNotNull('longitude'),
			user: $user,
			unlocked_album_ids: $unlocked_album_ids,
			origin: null,
			include_nsfw: !app(ConfigManager::class)->getValueAsBool('hide_nsfw_in_map'),
		);

		return $query;
	}

	/**
	 * Reproduces {@see \App\Actions\Album\PositionData::get()}'s exact
	 * `$album->photos()`/`$album->all_photos()` branching, minus eager
	 * loads and `->get()`. Return type mirrors
	 * {@see AbstractAlbum::photos()}'s own `Relation|Builder` — a real
	 * `Album`'s `photos()`/`all_photos()` return a `Relation` subclass whose
	 * own query-builder passthrough methods (`whereNotNull()` included)
	 * return `$this`, not the underlying `FixedQueryBuilder`.
	 *
	 * @return Relation<Photo,AbstractAlbum&\Illuminate\Database\Eloquent\Model,mixed>|Builder<Photo>
	 */
	private function resolveAlbumQuery(AbstractAlbum $album, bool $include_sub_albums): Relation|Builder
	{
		$photo_relation = ($album instanceof Album && $include_sub_albums) ?
			$album->all_photos() :
			$album->photos();

		return $photo_relation
			->whereNotNull('latitude')
			->whereNotNull('longitude');
	}

	/**
	 * Applies `$snapped`'s bounding box as a `WHERE` filter, correctly
	 * handling an antimeridian-crossing viewport (`west > east`): the
	 * longitude filter becomes `(longitude >= west OR longitude <= east)`
	 * instead of `whereBetween()` (FR-067-08, Q-067-06). `$snapped` must
	 * already be grid-snapped ({@see MapViewport::snapToGrid()}) — this
	 * method does not snap it itself.
	 *
	 * @param Relation<Photo,AbstractAlbum&\Illuminate\Database\Eloquent\Model,mixed>|Builder<Photo> $query
	 */
	private function applyBoundingBoxFilter(Relation|Builder $query, MapViewport $snapped): void
	{
		$query->whereBetween('latitude', [$snapped->south, $snapped->north]);

		if ($snapped->west > $snapped->east) {
			$query->where(fn (Builder $q) => $q->where('longitude', '>=', $snapped->west)->orWhere('longitude', '<=', $snapped->east));
		} else {
			$query->whereBetween('longitude', [$snapped->west, $snapped->east]);
		}
	}
}
