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
use App\Http\Resources\V3\MapPhotoResource;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the base candidate photo query for the Map's root (cross-library)
 * and album (± sub-albums) scopes — the exact same `Photo` rows
 * {@see \App\Actions\Albums\PositionData::do()}/{@see \App\Actions\Album\PositionData::get()}
 * resolve today, minus their eager loads and `->get()` (FR-067-03, FR-067-04).
 * Shared by {@see QueryMapBuckets} and {@see QueryMapPhotos}, together with
 * the photo-row to {@see MapPhotoResource} mapping both tiers return.
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

	/**
	 * Maps raw photo rows (`id`/`title`/`taken_at`/`latitude`/`longitude`)
	 * to a {@see MapPhotoResource}, resolving each photo's containing album
	 * (Q-067-15), blanking titles for guests under `file_name_hidden`, and
	 * formatting `taken_at` via `date_format_sidebar_taken_at`. Shared by
	 * {@see QueryMapPhotos} and {@see QueryMapBuckets}' `singleton_photos`
	 * (FR-067-25).
	 *
	 * @param \stdClass[] $rows
	 */
	private function buildMapPhotoResource(array $rows, ?AbstractAlbum $album, ?User $user, bool $include_sub_albums): MapPhotoResource
	{
		if (count($rows) === 0) {
			return new MapPhotoResource(ids: [], album_ids: [], titles: [], taken_ats: [], latitudes: [], longitudes: []);
		}

		$photo_ids = array_map(static fn (\stdClass $row): string => (string) $row->id, $rows);
		$album_ids_by_photo_id = $this->resolveAlbumIds($photo_ids, $album, $include_sub_albums, $user);

		$hide_titles = request()->configs()->getValueAsBool('file_name_hidden') && $user === null;
		$taken_at_format = request()->configs()->getValueAsString('date_format_sidebar_taken_at');

		$ids = [];
		$album_ids = [];
		$titles = [];
		$taken_ats = [];
		$latitudes = [];
		$longitudes = [];

		foreach ($rows as $row) {
			$ids[] = (string) $row->id;
			$album_ids[] = $album_ids_by_photo_id[$row->id] ?? null;
			$titles[] = $hide_titles ? '' : (string) $row->title;
			$taken_ats[] = self::formatTakenAt($row->taken_at, $taken_at_format);
			$latitudes[] = (float) $row->latitude;
			$longitudes[] = (float) $row->longitude;
		}

		return new MapPhotoResource(
			ids: $ids,
			album_ids: $album_ids,
			titles: $titles,
			taken_ats: $taken_ats,
			latitudes: $latitudes,
			longitudes: $longitudes,
		);
	}

	/**
	 * Resolves each of `$photo_ids`' real, viewer-accessible containing
	 * album id (Q-067-15): a non-sub-album `Album` scope has exactly one
	 * candidate (the requested album itself, trivial); an `include_sub_albums`
	 * `Album` scope picks the in-scope album with the lowest `_lft` per
	 * photo; root scope has no natural album at all, so it picks the
	 * lowest viewer-accessible `album_id` among every album the photo
	 * belongs to, resolved entirely in SQL (no `Album` hydration).
	 *
	 * @param string[] $photo_ids
	 *
	 * @return array<string,string|null>
	 */
	private function resolveAlbumIds(array $photo_ids, ?AbstractAlbum $album, bool $include_sub_albums, ?User $user): array
	{
		if ($album instanceof Album) {
			return $include_sub_albums ?
				$this->resolveAlbumIdsForSubtree($photo_ids, $album) :
				array_fill_keys($photo_ids, $album->get_id());
		}

		if ($album !== null) {
			// TagAlbum/PersonAlbum/BaseSmartAlbum: no "natural" containing
			// album distinct from the requested scope itself.
			return array_fill_keys($photo_ids, $album->get_id());
		}

		return $this->resolveAlbumIdsForRoot($photo_ids, $user);
	}

	/**
	 * Album scope, `include_sub_albums=true`: joins `photo_album` ->
	 * `albums`, constrained to `$album`'s own already-authorized `_lft`/
	 * `_rgt` subtree (inclusive of `$album` itself, mirroring
	 * `PhotoQueryPolicy::applySearchabilityFilter()`'s own `origin`
	 * handling that `all_photos()` already relies on) - no separate
	 * per-sub-album access re-check, exactly mirroring `all_photos()`'s own
	 * existing, unchecked-per-descendant semantics. Deterministic tie-break:
	 * lowest `_lft` per photo, picked in PHP via "order then take first row
	 * per group" over the small, already-bounded row set (mirrors
	 * `QueryPhotoBuckets::queryLiveBuckets()`'s own use of that idiom).
	 *
	 * @param string[] $photo_ids
	 *
	 * @return array<string,string>
	 */
	private function resolveAlbumIdsForSubtree(array $photo_ids, Album $album): array
	{
		$rows = DB::table('photo_album')
			->join('albums', 'albums.id', '=', 'photo_album.album_id')
			->whereIn('photo_album.photo_id', $photo_ids)
			->where('albums._lft', '>=', $album->_lft)
			->where('albums._rgt', '<=', $album->_rgt)
			->orderBy('photo_album.photo_id')
			->orderBy('albums._lft')
			->select(['photo_album.photo_id as photo_id', 'photo_album.album_id as album_id'])
			->get();

		$result = [];
		foreach ($rows as $row) {
			if (!array_key_exists($row->photo_id, $result)) {
				$result[$row->photo_id] = $row->album_id;
			}
		}

		return $result;
	}

	/**
	 * Root scope: no "natural" album at all - candidates come from a
	 * cross-library `Photo::query()`, not a specific album join. Joins
	 * `photo_album` -> `base_albums` -> `computed_access_permissions`,
	 * applies {@see AlbumQueryPolicy::appendAccessibilityConditions()} (the
	 * pure query-builder form of `AlbumPolicy::canAccess()`), then collapses
	 * the resulting one-row-per-membership fan-out via `GROUP BY
	 * photo_album.photo_id` + `MIN(photo_album.album_id)` - deterministic,
	 * no preference for "which scope asked" (Q-067-15, Option A). An admin
	 * bypasses the accessibility join entirely (every album is
	 * "accessible" to them), mirroring every other policy method's own
	 * admin short-circuit.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return array<string,string>
	 */
	private function resolveAlbumIdsForRoot(array $photo_ids, ?User $user): array
	{
		if ($user?->may_administrate === true) {
			$rows = DB::table('photo_album')
				->whereIn('photo_id', $photo_ids)
				->groupBy('photo_id')
				->selectRaw('photo_id, MIN(album_id) as album_id')
				->get();

			$result = [];
			foreach ($rows as $row) {
				$result[$row->photo_id] = $row->album_id;
			}

			return $result;
		}

		/** @var AlbumQueryPolicy $album_query_policy */
		$album_query_policy = resolve(AlbumQueryPolicy::class);
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		$query = DB::table('photo_album')
			->join('base_albums', 'base_albums.id', '=', 'photo_album.album_id')
			->whereIn('photo_album.photo_id', $photo_ids);

		$album_query_policy->joinSubComputedAccessPermissions($query, 'photo_album.album_id', 'left', '', false, $user);
		$query->where(fn ($q) => $album_query_policy->appendAccessibilityConditions($q, $user, $unlocked_album_ids));

		$rows = $query
			->groupBy('photo_album.photo_id')
			->selectRaw('photo_album.photo_id as photo_id, MIN(photo_album.album_id) as album_id')
			->get();

		$result = [];
		foreach ($rows as $row) {
			$result[$row->photo_id] = $row->album_id;
		}

		return $result;
	}

	/**
	 * Formats a raw, un-cast `photos.taken_at` datetime string
	 * (`"Y-m-d H:i:s[.u]"`) against an admin-configured PHP `date()` format
	 * string, via `DateTime::createFromFormat()` - never Carbon (NFR-067-02).
	 */
	private static function formatTakenAt(?string $raw_taken_at, string $format): ?string
	{
		if ($raw_taken_at === null) {
			return null;
		}

		$date = \DateTime::createFromFormat('Y-m-d H:i:s.u', $raw_taken_at);
		$date = $date !== false ? $date : \DateTime::createFromFormat('Y-m-d H:i:s', $raw_taken_at);

		return $date === false ? null : $date->format($format);
	}
}
