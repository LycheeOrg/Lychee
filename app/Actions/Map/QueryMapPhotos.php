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
use Illuminate\Database\Query\Builder as BaseBuilder;

/**
 * Query logic for `GET /api/v3/Map/Photos` (FR-067-09..12). `toBase()`-only
 * throughout (no Eloquent hydration, no `size_variants` join, no
 * `should_downgrade` computation - Q-067-12).
 *
 * Superseded design (Q-067-13, amended): this used to group photos into the
 * same SQL grid `QueryMapBuckets` uses and only return a cell's photos when
 * that cell's own count was `<= LEAF_THRESHOLD` - a fixed grid can only ever
 * approximate real pixel-radius clustering, and it never reproduced the old
 * (v2/non-SoA) map's graduated cluster sizes with thumbnails (owner: "the
 * clustering threshold is too wide"). Now this returns every distinct photo
 * in the viewport with no grouping at all, but only when the viewport's
 * total count is `<= MAX_VIEWPORT_PHOTOS`; the frontend hands that raw list
 * straight to the same `leaflet.markercluster`-based clustering the v2 path
 * already uses, instead of pre-bucketing photos into a grid server-side.
 * Above the cap, this returns empty and the frontend falls back to
 * `/Map/buckets`' aggregate count badges. Cost is bounded by
 * `MAX_VIEWPORT_PHOTOS` rows fetched, never by total scope size (NFR-067-05).
 */
class QueryMapPhotos
{
	use ResolvesMapPhotoSource;

	public const MAX_VIEWPORT_PHOTOS = 500;

	public function do(?AbstractAlbum $album, ?User $user, MapViewport $viewport, bool $include_sub_albums): MapPhotoResource
	{
		$snapped = $viewport->snapToGrid();

		// A separate `count()` pre-check followed by an unbounded `get()`
		// would leave a window where a photo added/moved into the viewport
		// between the two queries pushes the actual row count past the cap -
		// violating it instead of just returning it late. Fetching at most
		// `MAX_VIEWPORT_PHOTOS + 1` rows in the one query both avoids that
		// race and avoids ever running a separate full `COUNT(*)` over a
		// potentially huge bounding box at low zoom.
		$rows = $this->buildDistinctPhotoRowsQuery($album, $user, $include_sub_albums, $snapped)
			->limit(self::MAX_VIEWPORT_PHOTOS + 1)
			->get()
			->all();

		if (count($rows) > self::MAX_VIEWPORT_PHOTOS) {
			$rows = [];
		}

		return $this->buildMapPhotoResource($rows, $album, $user, $include_sub_albums);
	}

	/**
	 * Every distinct photo in `$snapped`'s bounding box, `toBase()`-only, no
	 * grid grouping - the caller ({@see self::do()}) bounds this with its own
	 * `->limit(MAX_VIEWPORT_PHOTOS + 1)` and decides individual-vs-aggregate
	 * rendering from the fetched row count directly, rather than running a
	 * separate `count()` first (which would leave a TOCTOU window against
	 * concurrent writes). `->distinct()` collapses the row-per-membership fan-out an
	 * `include_sub_albums` album-scope query's own `photo_album`/`albums`
	 * joins can otherwise produce for a photo living in more than one
	 * in-scope sub-album (mirrors `Album\PositionData::get()`'s own
	 * pre-existing fan-out for the exact same join, left as-is there since
	 * it never dedupes either) - every selected column here is invariant
	 * per photo, not per membership, so a `SELECT DISTINCT` is a correct,
	 * cheap collapse.
	 */
	private function buildDistinctPhotoRowsQuery(?AbstractAlbum $album, ?User $user, bool $include_sub_albums, MapViewport $snapped): BaseBuilder
	{
		$query = $album === null ?
			$this->resolveRootQuery($user) :
			$this->resolveAlbumQuery($album, $include_sub_albums);
		$this->applyBoundingBoxFilter($query, $snapped);

		return $query
			->select([])
			->selectRaw('photos.id as id, photos.title as title, photos.taken_at as taken_at, photos.latitude as latitude, photos.longitude as longitude')
			->distinct()
			->toBase();
	}
}
