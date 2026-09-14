<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Map;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\MapViewport;
use App\Http\Resources\V3\MapPhotoResource;
use App\Models\Album;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query logic for `GET /api/v3/Map/Photos` (FR-067-09..12). Two-pass,
 * `toBase()`-only throughout (no Eloquent hydration, no `size_variants`
 * join, no `should_downgrade` computation - Q-067-12): an aggregate
 * pre-pass resolves which grid cells are "leaf" cells
 * (`HAVING COUNT(*) <= LEAF_THRESHOLD`), then a second pass fetches exactly
 * those cells' distinct photo rows, plus a separate join+collapse pass
 * resolving each photo's real, viewer-accessible containing album id
 * (Q-067-15). Cost is bounded by `(leaf cells in viewport) × LEAF_THRESHOLD`,
 * never by total scope size (NFR-067-05).
 */
class QueryMapPhotos
{
	use ResolvesMapPhotoSource;

	public const LEAF_THRESHOLD = 20;

	public function do(?AbstractAlbum $album, ?User $user, MapViewport $viewport, bool $include_sub_albums): MapPhotoResource
	{
		$snapped = $viewport->snapToGrid();
		$cell = $snapped->cellSize();

		$leaf_cells = $this->resolveLeafCells($album, $user, $include_sub_albums, $snapped, $cell);
		if (count($leaf_cells) === 0) {
			return new MapPhotoResource(ids: [], album_ids: [], titles: [], taken_ats: [], latitudes: [], longitudes: []);
		}

		$rows = $this->resolveLeafRows($album, $user, $include_sub_albums, $snapped, $leaf_cells, $cell);
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
	 * Pass 1: aggregate pre-pass resolving which grid cells are "leaf"
	 * cells (`count <= LEAF_THRESHOLD`), `toBase()`-only.
	 *
	 * @return array<int,array{lat_cell:float,lng_cell:float}>
	 */
	private function resolveLeafCells(?AbstractAlbum $album, ?User $user, bool $include_sub_albums, MapViewport $snapped, float $cell): array
	{
		$query = $album === null ?
			$this->resolveRootQuery($user) :
			$this->resolveAlbumQuery($album, $include_sub_albums);
		$this->applyBoundingBoxFilter($query, $snapped);

		$rows = $query
			->select([])
			->selectRaw('FLOOR(latitude / ?) as lat_cell, FLOOR(longitude / ?) as lng_cell', [$cell, $cell])
			->groupBy('lat_cell', 'lng_cell')
			->havingRaw('COUNT(*) <= ?', [self::LEAF_THRESHOLD])
			->toBase()
			->get();

		$leaf_cells = [];
		foreach ($rows as $row) {
			$leaf_cells[] = ['lat_cell' => (float) $row->lat_cell, 'lng_cell' => (float) $row->lng_cell];
		}

		return $leaf_cells;
	}

	/**
	 * Pass 2: distinct photo rows restricted to exactly the leaf cells
	 * resolved by {@see self::resolveLeafCells()} - `->distinct()` collapses
	 * the row-per-membership fan-out an `include_sub_albums` album-scope
	 * query's own `photo_album`/`albums` joins can otherwise produce for a
	 * photo living in more than one in-scope sub-album (mirrors
	 * `Album\PositionData::get()`'s own pre-existing fan-out for the exact
	 * same join, left as-is there since it never dedupes either) - every
	 * selected column here is invariant per photo, not per membership, so a
	 * `SELECT DISTINCT` is a correct, cheap collapse.
	 *
	 * @param array<int,array{lat_cell:float,lng_cell:float}> $leaf_cells
	 *
	 * @return array<int,\stdClass>
	 */
	private function resolveLeafRows(?AbstractAlbum $album, ?User $user, bool $include_sub_albums, MapViewport $snapped, array $leaf_cells, float $cell): array
	{
		$query = $album === null ?
			$this->resolveRootQuery($user) :
			$this->resolveAlbumQuery($album, $include_sub_albums);
		$this->applyBoundingBoxFilter($query, $snapped);

		$query->where(function (Builder $outer) use ($leaf_cells, $cell): void {
			foreach ($leaf_cells as $leaf_cell) {
				$lat_lo = $leaf_cell['lat_cell'] * $cell;
				$lat_hi = ($leaf_cell['lat_cell'] + 1) * $cell;
				$lng_lo = $leaf_cell['lng_cell'] * $cell;
				$lng_hi = ($leaf_cell['lng_cell'] + 1) * $cell;

				$outer->orWhere(function (Builder $inner) use ($lat_lo, $lat_hi, $lng_lo, $lng_hi): void {
					$inner
						->where('latitude', '>=', $lat_lo)
						->where('latitude', '<', $lat_hi)
						->where('longitude', '>=', $lng_lo)
						->where('longitude', '<', $lng_hi);
				});
			}
		});

		return $query
			->select([])
			->selectRaw('photos.id as id, photos.title as title, photos.taken_at as taken_at, photos.latitude as latitude, photos.longitude as longitude')
			->distinct()
			->toBase()
			->get()
			->all();
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
