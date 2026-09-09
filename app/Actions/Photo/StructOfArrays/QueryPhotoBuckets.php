<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Constants\PhotoAlbum as PA;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Http\Resources\V3\PhotoBucketResource;
use App\Models\Album;
use App\Models\Extensions\FiltersUploadValidation;
use App\Models\Photo;
use App\Models\User;
use App\Services\PhotoBucketComputer;
use function Safe\mktime;

/**
 * Query logic for `GET /api/v3/Albums/{album_id}/Photos/buckets`. Direct
 * structural precedent:
 * {@see \App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController::queryBuckets()}.
 */
class QueryPhotoBuckets
{
	use FiltersUploadValidation;

	public function __construct(
		private readonly PhotoBucketComputer $bucket_computer,
	) {
	}

	public function do(Album $album, ?User $user): PhotoBucketResource
	{
		$sorting = $album->getEffectivePhotoSorting();

		// OWNER_ID is excluded from photo bucketing entirely, per explicit
		// user direction - short-circuit without ever running a GROUP BY.
		if ($sorting->column === ColumnSortingType::OWNER_ID) {
			return new PhotoBucketResource(bucket_ids: [], counts: [], labels: [], bucketable: false);
		}

		$query = Photo::query()
			->join(PA::PHOTO_ALBUM, PA::PHOTO_ID, '=', 'photos.id')
			->where(PA::ALBUM_ID, '=', $album->id);

		// Non-admins must not see unvalidated photos uploaded by other
		// users - including in bucket counts.
		if ($user?->may_administrate !== true) {
			$this->applyUploadValidationFilter($query, $user?->id);
		}

		$direction = $sorting->order === OrderSortingType::DESC ? 'desc' : 'asc';

		$rows = $query
			->select(['photo_album.bucket_id'])
			->selectRaw('COUNT(*) as bucket_count')
			->groupBy('photo_album.bucket_id')
			// NULL ("unknown") always sorts last, regardless of $direction.
			->orderByRaw('(photo_album.bucket_id IS NULL) ASC')
			->orderBy('photo_album.bucket_id', $direction)
			->toBase()
			->get();

		$bucket_ids = [];
		$counts = [];
		foreach ($rows as $row) {
			$bucket_ids[] = $row->bucket_id ?? 'unknown';
			$counts[] = (int) $row->bucket_count;
		}

		$labels = $this->computeLabels($bucket_ids, $sorting->column, $album->photo_timeline);

		return new PhotoBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $labels, bucketable: true);
	}

	/**
	 * Computes one display label per distinct bucket — bounded by bucket
	 * count, never by photo-row count: it runs entirely in PHP, after the
	 * `GROUP BY`.
	 *
	 * @param string[] $bucket_ids
	 *
	 * @return string[]
	 */
	private function computeLabels(array $bucket_ids, ColumnSortingType $sorting_column, ?TimelinePhotoGranularity $photo_timeline): array
	{
		$is_alphabetical_title = $sorting_column === ColumnSortingType::TITLE &&
			(request()->configs()->getValueAsEnum('photo_title_bucket_mode', TitleBucketMode::class) ?? TitleBucketMode::DATE_PREFIX) === TitleBucketMode::ALPHABETICAL;

		if ($is_alphabetical_title) {
			// Already human-readable; never date-parsed.
			return $bucket_ids;
		}

		// IS_HIGHLIGHTED ("1"/"0") and RATING_AVG ("0".."5") are also
		// already primitive/human-parseable - translation to e.g.
		// "Highlighted"/"★★★" is the frontend's job.
		if ($sorting_column === ColumnSortingType::IS_HIGHLIGHTED || $sorting_column === ColumnSortingType::TYPE || $sorting_column === ColumnSortingType::RATING_AVG) {
			return $bucket_ids;
		}

		$granularity = $this->bucket_computer->resolveGranularity($photo_timeline);
		$format = match ($granularity) {
			TimelinePhotoGranularity::YEAR => request()->configs()->getValueAsString('timeline_photo_date_format_year'),
			TimelinePhotoGranularity::MONTH => request()->configs()->getValueAsString('timeline_photo_date_format_month'),
			TimelinePhotoGranularity::DAY => request()->configs()->getValueAsString('timeline_photo_date_format_day'),
			TimelinePhotoGranularity::HOUR => request()->configs()->getValueAsString('timeline_photo_date_format_hour'),
			default => request()->configs()->getValueAsString('timeline_photo_date_format_year'),
		};

		return array_map(
			fn (string $bucket_id): string => $bucket_id === 'unknown' ? 'unknown' : $this->formatBucketLabel($bucket_id, $format),
			$bucket_ids,
		);
	}

	/**
	 * Formats one `bucket_id` (`"Y"`/`"Y-m"`/`"Y-m-d"`/`"Y-m-d-H"`, the exact
	 * truncation format {@see PhotoBucketComputer::truncateDate()} writes)
	 * against an arbitrary admin-configured PHP `date()` format string — via
	 * `mktime()` + `date()`, not a `Carbon`/`DateTime` object - the same
	 * no-Carbon discipline applied here to label formatting too.
	 */
	private function formatBucketLabel(string $bucket_id, string $format): string
	{
		$parts = explode('-', $bucket_id);
		$year = (int) $parts[0];
		$month = (int) ($parts[1] ?? 1);
		$day = (int) ($parts[2] ?? 1);
		$hour = (int) ($parts[3] ?? 0);

		return date($format, mktime($hour, 0, 0, $month, $day, $year));
	}
}
