<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\PhotoSortingCriterion;
use App\Eloquent\FixedQueryBuilder;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\FiltersUploadValidation;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Models\User;
use App\Services\BucketScheme;
use App\Services\PhotoBucketComputer;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\TimelineAlbum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Resolves the base photo query for any {@see AbstractAlbum} implementation
 * — a regular {@see Album}, a {@see \App\Models\TagAlbum}, a
 * {@see \App\Models\PersonAlbum}, or a {@see BaseSmartAlbum} — shared by
 * {@see QueryPhotoBuckets}, {@see QueryPhotoRatios} and
 * {@see QueryPhotoDetails}.
 *
 * Only a regular `Album` has a `photo_album` pivot row per photo (and
 * therefore a stored `bucket_key`). The other three types have no such row —
 * `TagAlbum`/`PersonAlbum` membership resolves via `photos_tags`/face
 * matching, and `BaseSmartAlbum`'s own `leftJoin` to `photo_album` is
 * unscoped access-control plumbing, not a per-photo bucket source — so they
 * are resolved through their own existing `photos()` accessor instead,
 * which already applies membership/visibility filtering, and their
 * bucket id is computed in SQL from photo columns instead
 * ({@see BucketScheme::sourceIdSql()}, FR-092-06).
 */
trait ResolvesPhotoSource
{
	use FiltersUploadValidation;

	/**
	 * `$scope` (a bucket window or an id list on `photos.*` columns) restricts
	 * the returned query; for a smart album it also restricts the visible-photo
	 * id subquery, which would otherwise list every visible photo of the
	 * library on each request (FR-103-09).
	 *
	 * @param (\Closure(FixedQueryBuilder<Photo>): void)|null $scope
	 *
	 * @return FixedQueryBuilder<Photo>
	 */
	private function resolvePhotoQuery(AbstractAlbum $album, ?User $user, ?\Closure $scope = null): FixedQueryBuilder
	{
		if ($album instanceof Album) {
			$query = Photo::query()
				->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
				->where(PA::ALBUM_ID, '=', $album->id);
		} elseif ($album instanceof BaseSmartAlbum) {
			// `BaseSmartAlbum::photos()` applies its visibility filter
			// directly on a query that already carries an un-scoped
			// `leftJoin(photo_album, ...)` (no album_id restriction -
			// BaseSmartAlbum.php's own `photos()`) - a photo belonging to N
			// regular albums therefore fans out to N rows there. This is
			// exactly the duplication `HasManyPhotosByTag::addEagerConstraints()`'s
			// own doc comment describes and deliberately avoids, via a
			// `whereIn` id-subquery instead of joining directly on the
			// outer/returned query - mirrored here for the same reason: the
			// query this method returns must carry exactly one row per
			// `photos.id`, regardless of how many regular albums a photo
			// belongs to.
			$matching_ids = $album->photos()->select('photos.id');
			if ($scope !== null) {
				$scope($matching_ids);
			}
			$query = Photo::query()->whereIn('photos.id', $matching_ids);
		} else {
			// TagAlbum | PersonAlbum - `photos()` returns a `Relation`
			// (`HasManyPhotosByTag`/`HasManyPhotosByPerson`); `getQuery()`
			// unwraps it to the underlying `PhotoBuilder<Photo>`
			// (mirrors `BaseHasManyPhotos::getRelationQuery()`, which is
			// `protected` and therefore not reachable from here directly).
			/** @var Relation<Photo,AbstractAlbum&Model,Collection<int,Photo>> $relation */
			$relation = $album->photos();
			$query = $relation->getQuery();
		}

		if ($user?->may_administrate !== true) {
			$this->applyUploadValidationFilter($query, $user?->id);
		}

		if ($scope !== null) {
			$scope($query);
		}

		return $query;
	}

	/**
	 * `getEffectivePhotoSorting()` is only defined on `Album`/`TagAlbum`/
	 * `PersonAlbum` (via `App\Models\Extensions\BaseAlbum`) - a
	 * `BaseSmartAlbum` has no per-album override and always resolves to the
	 * instance-wide default instead, mirroring `BaseSmartAlbum`'s own
	 * internal use of {@see PhotoSortingCriterion::createDefault()}.
	 *
	 * `TimelineAlbum` is the one `BaseSmartAlbum` with its own,
	 * longstanding sort-config key (`timeline_photos_order`) - reproduces
	 * {@see \App\Actions\Photo\Timeline::do()}'s sort resolution exactly:
	 * restricted to `{CREATED_AT, TAKEN_AT}` (silently falling back to
	 * `TAKEN_AT` for any other configured value), direction always `DESC`.
	 */
	private function resolveEffectiveSorting(AbstractAlbum $album): PhotoSortingCriterion
	{
		if ($album instanceof TimelineAlbum) {
			$order = request()->configs()->getValueAsEnum('timeline_photos_order', ColumnSortingPhotoType::class);

			if (!in_array($order, [ColumnSortingPhotoType::CREATED_AT, ColumnSortingPhotoType::TAKEN_AT], true)) {
				$order = ColumnSortingPhotoType::TAKEN_AT;
			}

			return new PhotoSortingCriterion($order->toColumnSortingType(), OrderSortingType::DESC);
		}

		if ($album instanceof BaseAlbum) {
			return $album->getEffectivePhotoSorting();
		}

		return PhotoSortingCriterion::createDefault();
	}

	/**
	 * `photo_timeline` (the per-album granularity override) only exists on
	 * `Album`/`TagAlbum`/`PersonAlbum` - a `BaseSmartAlbum` has no such
	 * property, so `null` (-> the instance-wide default, resolved by
	 * {@see \App\Services\PhotoBucketComputer::resolveGranularity()}) is
	 * always used for it.
	 */
	private function resolvePhotoTimeline(AbstractAlbum $album): ?TimelinePhotoGranularity
	{
		return $album instanceof BaseAlbum ? $album->photo_timeline : null;
	}

	/**
	 * Read-time bucket scheme of `$album` (FR-092-05, FR-092-06): its
	 * effective sort column and resolved granularity.
	 */
	private function resolveScheme(AbstractAlbum $album, PhotoSortingCriterion $sorting): BucketScheme
	{
		return resolve(PhotoBucketComputer::class)->scheme($sorting->column, $this->resolvePhotoTimeline($album));
	}

	/**
	 * SQL bucket id of a row of {@see self::resolvePhotoQuery()}: a prefix of
	 * the stored `photo_album.bucket_key` for a regular album, computed from
	 * photo columns for every other source.
	 */
	private function bucketIdSql(AbstractAlbum $album, BucketScheme $scheme): string
	{
		return $album instanceof Album ? $scheme->storedIdSql(PA::BUCKET_KEY) : $scheme->sourceIdSql();
	}

	/**
	 * Restricts a query of {@see self::resolvePhotoQuery()} to `$bucket_ids`
	 * (FR-092-07).
	 *
	 * @param FixedQueryBuilder<Photo> $query
	 * @param string[]                 $bucket_ids
	 */
	private function applyBucketWindow(FixedQueryBuilder $query, AbstractAlbum $album, BucketScheme $scheme, array $bucket_ids): void
	{
		if (!$scheme->isBucketable()) {
			// Every row of an unbucketable source is `unknown`.
			$query->whereRaw(in_array(BucketScheme::UNKNOWN, $bucket_ids, true) ? '1 = 1' : '1 = 0');

			return;
		}

		$query->where($album instanceof Album ? $scheme->storedWindow(PA::BUCKET_KEY, $bucket_ids) : $scheme->sourceWindow($bucket_ids));
	}

	/**
	 * Orders rows by bucket (`unknown` last), then by the effective sort
	 * criterion, replacing any ordering the source query carries, so grouping rows by bucket id reproduces the `buckets`
	 * tier (FR-092-07).
	 *
	 * @param FixedQueryBuilder<Photo> $query
	 */
	private function orderByBucket(FixedQueryBuilder $query, AbstractAlbum $album, BucketScheme $scheme, PhotoSortingCriterion $sorting): void
	{
		// A tag/person relation query arrives already ordered by the album
		// sort: the bucket order must come first.
		$query->reorder();
		if ($scheme->isBucketable()) {
			$scheme->orderRows($query, $this->bucketIdSql($album, $scheme), $sorting->order);
		}
		(new SortingDecorator($query))->orderPhotosBy($sorting->column, $sorting->order)->applyOrdering();
	}
}
