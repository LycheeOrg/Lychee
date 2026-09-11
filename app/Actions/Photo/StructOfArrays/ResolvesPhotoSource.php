<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Assets\DbBool;
use App\Constants\PhotoAlbum as PA;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\PhotoSortingCriterion;
use App\Eloquent\FixedQueryBuilder;
use App\Enum\ColumnSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Exceptions\Internal\LycheeLogicException;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\FiltersUploadValidation;
use App\Models\Photo;
use App\Models\User;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Relations\Relation;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;

/**
 * Resolves the base photo query for any {@see AbstractAlbum} implementation
 * — a regular {@see Album}, a {@see \App\Models\TagAlbum}, a
 * {@see \App\Models\PersonAlbum}, or a {@see BaseSmartAlbum} — shared by
 * {@see QueryPhotoBuckets}, {@see QueryPhotoRatios} and
 * {@see QueryPhotoDetails}.
 *
 * Only a regular `Album` has a `photo_album` pivot row per photo (and
 * therefore a stored `bucket_id`). The other three types have no such row —
 * `TagAlbum`/`PersonAlbum` membership resolves via `photos_tags`/face
 * matching, and `BaseSmartAlbum`'s own `leftJoin` to `photo_album` is
 * unscoped access-control plumbing, not a per-photo bucket source — so they
 * are resolved through their own existing `photos()` accessor instead,
 * which already applies membership/visibility filtering, and their
 * `bucket_id` must be computed live instead (see {@see self::liveBucketId()}).
 */
trait ResolvesPhotoSource
{
	use FiltersUploadValidation;

	/**
	 * @return FixedQueryBuilder<Photo>
	 */
	private function resolvePhotoQuery(AbstractAlbum $album, ?User $user): FixedQueryBuilder
	{
		if ($album instanceof Album) {
			$query = Photo::query()
				->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
				->where(PA::ALBUM_ID, '=', $album->id);
		} elseif ($album instanceof BaseSmartAlbum) {
			// Already a `FixedQueryBuilder<Photo>` at runtime (built off
			// `Photo::query()`) - only typed as the looser `Builder` by
			// `BaseSmartAlbum::photos()`'s own signature.
			/** @var FixedQueryBuilder<Photo> $query */
			$query = $album->photos();
		} else {
			// TagAlbum | PersonAlbum - `photos()` returns a `Relation`
			// (`HasManyPhotosByTag`/`HasManyPhotosByPerson`); `getQuery()`
			// unwraps it to the underlying `FixedQueryBuilder<Photo>`
			// (mirrors `BaseHasManyPhotos::getRelationQuery()`, which is
			// `protected` and therefore not reachable from here directly).
			/** @var Relation<Photo,BaseAlbum,mixed> $relation */
			$relation = $album->photos();
			/** @var FixedQueryBuilder<Photo> $query */
			$query = $relation->getQuery();
		}

		if ($user?->may_administrate !== true) {
			$this->applyUploadValidationFilter($query, $user?->id);
		}

		return $query;
	}

	/**
	 * `getEffectivePhotoSorting()` is only defined on `Album`/`TagAlbum`/
	 * `PersonAlbum` (via `App\Models\Extensions\BaseAlbum`) - a
	 * `BaseSmartAlbum` has no per-album override and always resolves to the
	 * instance-wide default instead, mirroring `BaseSmartAlbum`'s own
	 * internal use of {@see PhotoSortingCriterion::createDefault()}.
	 */
	private function resolveEffectiveSorting(AbstractAlbum $album): PhotoSortingCriterion
	{
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
	 * Computes one row's live `bucket_id`, given the raw scalar columns
	 * selected via {@see self::resolvePhotoQuery()} (`title`, `title_base`,
	 * `created_at`, `taken_at`, `is_highlighted`, `type`, `rating_avg`),
	 * mirroring {@see \App\Services\PhotoBucketComputer::compute()}'s write-path logic
	 * exactly - but deliberately reimplemented Carbon-free (raw string
	 * truncation instead of `Carbon::parse()` per row), the same "no Carbon
	 * in this request-path tier" discipline {@see QueryPhotoBuckets::formatBucketLabel()}
	 * already documents and follows, here applied to a call that runs once
	 * per candidate photo rather than once per distinct bucket.
	 */
	private function liveBucketId(ColumnSortingType $column, TimelinePhotoGranularity $granularity, \stdClass $row): ?string
	{
		if ($column === ColumnSortingType::OWNER_ID) {
			return null;
		}

		if ($column === ColumnSortingType::TITLE) {
			return $this->liveTitleBucket($granularity, $row->title ?? '', $row->title_base ?? '');
		}

		if ($column === ColumnSortingType::IS_HIGHLIGHTED) {
			return DbBool::parse($row->is_highlighted) ? '1' : '0';
		}

		if ($column === ColumnSortingType::TYPE) {
			return $row->type ?? '';
		}

		if ($column === ColumnSortingType::RATING_AVG) {
			return $row->rating_avg === null ? null : (string) round((float) $row->rating_avg);
		}

		$raw_date = match ($column) {
			ColumnSortingType::CREATED_AT => $row->created_at,
			ColumnSortingType::TAKEN_AT => $row->taken_at,
			default => null,
		};

		return $raw_date === null ? null : $this->truncateRawDate((string) $raw_date, $granularity);
	}

	/**
	 * Bucket computation for a `TITLE`-sorted, non-`Album` source, mirroring
	 * {@see \App\Services\PhotoBucketComputer::computeTitleBucket()} - the date-prefix
	 * branch reimplements {@see \App\Http\Resources\Models\Utils\TimelineData::parseDateFromTitle()}'s
	 * own regex directly rather than calling it, purely to avoid the
	 * `Carbon` instance that helper constructs and immediately discards
	 * (only `$year`/`$month`/`$day` are ever used here).
	 */
	private function liveTitleBucket(TimelinePhotoGranularity $granularity, string $title, string $title_base): ?string
	{
		$mode = request()->configs()->getValueAsEnum('photo_title_bucket_mode', TitleBucketMode::class) ?? TitleBucketMode::DATE_PREFIX;

		if ($mode === TitleBucketMode::ALPHABETICAL) {
			$length = max(1, request()->configs()->getValueAsInt('photo_title_bucket_prefix_length'));

			return mb_substr($title_base, 0, $length);
		}

		try {
			if (preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?/', trim($title), $matches) !== 1) {
				return null;
			}
			// @codeCoverageIgnoreStart
		} catch (PcreException) {
			// Mirrors TimelineData::parseDateFromTitle()'s own "fail
			// silently" handling of this same pattern.
			return null;
		}
		// @codeCoverageIgnoreEnd

		$year = $matches[1];
		$month = $matches[2] ?? '01';
		$day = $matches[3] ?? '01';

		return match ($granularity) {
			TimelinePhotoGranularity::YEAR => $year,
			TimelinePhotoGranularity::MONTH => $year . '-' . $month,
			TimelinePhotoGranularity::DAY, TimelinePhotoGranularity::HOUR => $year . '-' . $month . '-' . $day,
			// @codeCoverageIgnoreStart
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid resolved granularity for photos'),
			// @codeCoverageIgnoreEnd
		};
	}

	/**
	 * Truncates a raw, un-cast `photos.created_at`/`photos.taken_at`
	 * datetime string (`"Y-m-d H:i:s[.u]"`, the exact form every supported
	 * DB driver's PDO layer returns for a `toBase()` row) at `$granularity`,
	 * matching {@see \App\Services\PhotoBucketComputer::truncateDate()}'s output format
	 * byte-for-byte - via plain substring slicing, deliberately never a
	 * `Carbon`/`DateTime` object.
	 */
	private function truncateRawDate(string $raw_date, TimelinePhotoGranularity $granularity): string
	{
		return match ($granularity) {
			TimelinePhotoGranularity::YEAR => substr($raw_date, 0, 4),
			TimelinePhotoGranularity::MONTH => substr($raw_date, 0, 7),
			TimelinePhotoGranularity::DAY => substr($raw_date, 0, 10),
			TimelinePhotoGranularity::HOUR => substr($raw_date, 0, 10) . '-' . substr($raw_date, 11, 2),
			// @codeCoverageIgnoreStart
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid resolved granularity for photos'),
			// @codeCoverageIgnoreEnd
		};
	}
}
