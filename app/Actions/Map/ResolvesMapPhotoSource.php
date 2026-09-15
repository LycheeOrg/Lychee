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
use Illuminate\Database\Query\Builder as BaseBuilder;

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
	 * Also clears any `ORDER BY` the query already carries: an
	 * `include_sub_albums`-scope query built via `$album->all_photos()`
	 * (`HasManyPhotosRecursively::addEagerConstraints()`) bakes in an
	 * `ORDER BY <effective sort column>` as a side effect of resolving the
	 * relation, which is meaningless for the `GROUP BY` aggregate queries
	 * both {@see QueryMapBuckets} and {@see QueryMapPhotos} build on top of
	 * this method — and actively breaks under PostgreSQL, which (unlike
	 * sqlite) rejects an `ORDER BY` column that is neither grouped nor
	 * aggregated.
	 *
	 * @param Relation<Photo,AbstractAlbum&\Illuminate\Database\Eloquent\Model,mixed>|Builder<Photo> $query
	 */
	private function applyBoundingBoxFilter(Relation|Builder $query, MapViewport $snapped): void
	{
		$query->reorder();

		$query->whereBetween('latitude', [$snapped->south, $snapped->north]);

		if ($snapped->west > $snapped->east) {
			$query->where(fn (Builder $q) => $q->where('longitude', '>=', $snapped->west)->orWhere('longitude', '<=', $snapped->east));
		} else {
			$query->whereBetween('longitude', [$snapped->west, $snapped->east]);
		}
	}

	/**
	 * Collapses `$query` (already bounding-box-filtered) down to one row per
	 * distinct photo — `photos.id`/`latitude`/`longitude` only, all three
	 * invariant per photo, never per membership. Required before any
	 * grid-cell aggregation: both {@see \App\Actions\Albums\PositionData::do()}'s
	 * own `applySearchabilityFilter()` (root scope) and `all_photos()`
	 * (album scope with `include_sub_albums`) `LEFT JOIN albums`
	 * unconditionally, so a photo linked into more than one album within
	 * scope fans out into one row per membership — left as-is, a plain
	 * `COUNT(*)`/`GROUP BY` over that fanned-out row set would double-count
	 * such a photo in both its bucket's count and its cell's leaf-threshold
	 * check. Returns the caller's own query builder type unchanged
	 * (`toBase()`-only, no dedication to a specific subquery shape) so a
	 * caller can wrap it with `DB::query()->fromSub(...)` before running its
	 * own `GROUP BY` on top of the now-deduplicated row set.
	 *
	 * @param Relation<Photo,AbstractAlbum&\Illuminate\Database\Eloquent\Model,mixed>|Builder<Photo> $query
	 */
	private function resolveDistinctPhotoRows(Relation|Builder $query): BaseBuilder
	{
		return $query
			->select([])
			->selectRaw('photos.id as id, photos.latitude as latitude, photos.longitude as longitude')
			->distinct()
			->toBase();
	}
}
