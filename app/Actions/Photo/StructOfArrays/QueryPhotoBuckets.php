<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Resources\V3\PhotoBucketResource;
use App\Models\Album;
use App\Models\User;
use App\Services\BucketScheme;
use App\SmartAlbums\TimelineAlbum;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query logic for `GET /api/v3/Albums/{album_id}/Photos/buckets`: one
 * `GROUP BY` on the bucket id of {@see BucketScheme} for every source
 * (FR-092-05, FR-092-06), labels by id precision (FR-092-08).
 */
class QueryPhotoBuckets
{
	use ResolvesPhotoSource;

	public function do(AbstractAlbum $album, ?User $user): PhotoBucketResource
	{
		$sorting = $this->resolveEffectiveSorting($album);
		$scheme = $this->resolveScheme($album, $sorting);

		// OWNER_ID is not bucketable for photos - no GROUP BY at all.
		if (!$scheme->isBucketable()) {
			return new PhotoBucketResource(bucket_ids: [], counts: [], labels: [], bucketable: false);
		}

		$id_sql = $this->bucketIdSql($album, $scheme);
		$query = $album instanceof Album
			? $this->storedKeyQuery($album, $user)
			// A tag/person relation query arrives ordered by the album sort,
			// which a grouped query must not keep.
			: $this->resolvePhotoQuery($album, $user)->toBase()->reorder();

		if ($album instanceof TimelineAlbum) {
			// The Timeline is date-first: a photo without a value in its sort
			// column has no position on it and is excluded entirely.
			$query->whereNotNull($scheme->sourceColumn());
		}

		$query->select(DB::raw($id_sql . ' as bucket_id'))
			->selectRaw('COUNT(*) as bucket_count')
			->groupByRaw($id_sql);
		$scheme->orderRows($query, $id_sql, $sorting->order);

		[$bucket_ids, $counts] = $scheme->collectBuckets($query->get());

		return new PhotoBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $scheme->labels($bucket_ids), bucketable: true);
	}

	/**
	 * `photo_album` rows of a regular album, without joining `photos` per
	 * link (FR-092-09): a viewer without `may_administrate` loses the links
	 * to unvalidated photos of other owners (every owner, for a guest)
	 * through a subquery on the few unvalidated photos.
	 */
	private function storedKeyQuery(Album $album, ?User $user): Builder
	{
		$query = DB::table(PA::PHOTO_ALBUM)->where(PA::ALBUM_ID, '=', $album->id);

		if ($user?->may_administrate !== true) {
			$query->whereNotIn(PA::PHOTO_ID, function (Builder $unvalidated) use ($user): void {
				$unvalidated->select('photos.id')
					->from('photos')
					->where('photos.is_validated', '=', false);
				if ($user !== null) {
					$unvalidated->where('photos.owner_id', '<>', $user->id);
				}
			});
		}

		return $query;
	}
}
