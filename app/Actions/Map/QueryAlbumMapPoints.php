<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Map;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Resources\V3\MapPointResource;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Query logic for `GET /api/v3/Map/album` and the album map header
 * decision (Feature 086, ADR-086-01). Same photo scope as the album's Map
 * page ({@see ResolvesMapPhotoSource::resolveAlbumQuery()}), without
 * viewport, clustering or cap.
 */
class QueryAlbumMapPoints
{
	use ResolvesMapPhotoSource;

	/**
	 * Photo ids per album-id lookup, kept under every database's bound
	 * parameter limit (SQLite: 32766).
	 */
	private const ALBUM_ID_CHUNK = 1000;

	public function do(AbstractAlbum $album, ?User $user, bool $include_sub_albums): MapPointResource
	{
		$rows = $this->resolveDistinctPhotoRows($this->scopedQuery($album, $include_sub_albums))->get()->all();

		$ids = array_map(static fn (\stdClass $row): string => (string) $row->id, $rows);
		$album_ids_by_photo_id = [];
		foreach (array_chunk($ids, self::ALBUM_ID_CHUNK) as $chunk) {
			$album_ids_by_photo_id += $this->resolveAlbumIds($chunk, $album, $include_sub_albums, $user);
		}

		return new MapPointResource(
			ids: $ids,
			album_ids: array_map(static fn (string $id): ?string => $album_ids_by_photo_id[$id] ?? null, $ids),
			latitudes: array_map(static fn (\stdClass $row): float => (float) $row->latitude, $rows),
			longitudes: array_map(static fn (\stdClass $row): float => (float) $row->longitude, $rows),
		);
	}

	/**
	 * Whether the album has at least one geotagged photo in scope.
	 */
	public function hasPoints(AbstractAlbum $album, bool $include_sub_albums): bool
	{
		return $this->scopedQuery($album, $include_sub_albums)->exists();
	}

	/**
	 * @return Relation<Photo,AbstractAlbum&\Illuminate\Database\Eloquent\Model,mixed>|Builder<Photo>
	 */
	private function scopedQuery(AbstractAlbum $album, bool $include_sub_albums): Relation|Builder
	{
		// `all_photos()` bakes in an ORDER BY that PostgreSQL rejects next to DISTINCT.
		return $this->resolveAlbumQuery($album, $include_sub_albums)->reorder();
	}
}
