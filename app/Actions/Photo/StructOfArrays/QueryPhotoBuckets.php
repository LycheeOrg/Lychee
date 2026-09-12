<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\StructOfArrays;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Http\Resources\V3\PhotoBucketResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
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
	use ResolvesPhotoSource;

	private const LIVE_BUCKET_COLUMNS = [
		'photos.title', 'photos.title_base', 'photos.created_at', 'photos.taken_at',
		'photos.is_highlighted', 'photos.type', 'photos.rating_avg',
	];

	public function __construct(
		private readonly PhotoBucketComputer $bucket_computer,
	) {
	}

	public function do(AbstractAlbum $album, ?User $user): PhotoBucketResource
	{
		$sorting = $this->resolveEffectiveSorting($album);

		// OWNER_ID is excluded from photo bucketing entirely, per explicit
		// user direction - short-circuit without ever running a GROUP BY.
		if ($sorting->column === ColumnSortingType::OWNER_ID) {
			return new PhotoBucketResource(bucket_ids: [], counts: [], labels: [], bucketable: false);
		}

		if ($album instanceof Album) {
			[$bucket_ids, $counts] = $this->queryStoredBuckets($album, $user, $sorting->order);
		} else {
			[$bucket_ids, $counts] = $this->queryLiveBuckets($album, $user, $sorting->column, $sorting->order);
		}

		$labels = $this->computeLabels($bucket_ids, $sorting->column, $this->resolvePhotoTimeline($album));

		return new PhotoBucketResource(bucket_ids: $bucket_ids, counts: $counts, labels: $labels, bucketable: true);
	}

	/**
	 * @return array{0:string[],1:int[]}
	 */
	private function queryStoredBuckets(Album $album, ?User $user, OrderSortingType $order): array
	{
		$query = $this->resolvePhotoQuery($album, $user);
		$direction = $order === OrderSortingType::DESC ? 'desc' : 'asc';

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

		return [$bucket_ids, $counts];
	}

	/**
	 * `TagAlbum`/`PersonAlbum`/`BaseSmartAlbum` have no stored `bucket_id`
	 * to `GROUP BY` (see {@see ResolvesPhotoSource}) - every candidate row's
	 * bucket is computed live instead, then grouped in PHP after the query
	 * (bounded by this album's own photo count, exactly the scale the
	 * un-grouped `Album` case already visits for {@see QueryPhotoRatios}).
	 *
	 * Grouped by run-length counting over the already-ordered rows, never
	 * by keying a PHP array on the `bucket_id` string itself: PHP silently
	 * casts a canonical-integer-looking string key (e.g. `"2026"`, a
	 * YEAR-granularity date bucket, or `"1"`/`"0"` for `IS_HIGHLIGHTED`) to
	 * an actual `int` array key, which would corrupt the `string[]`
	 * `bucket_ids` contract on the way out to JSON. This is safe because
	 * rows sharing the same live-computed bucket are always contiguous in
	 * this SQL-ordered result - `bucket_id` is a deterministic,
	 * order-preserving function of the exact column being sorted on for
	 * every supported sort column (date truncation, rounding, or a direct
	 * column value).
	 *
	 * @return array{0:string[],1:int[]}
	 */
	private function queryLiveBuckets(AbstractAlbum $album, ?User $user, ColumnSortingType $column, OrderSortingType $order): array
	{
		$query = $this->resolvePhotoQuery($album, $user);
		(new SortingDecorator($query))->orderPhotosBy($column, $order)->applyOrdering();

		$rows = $query->select(self::LIVE_BUCKET_COLUMNS)->toBase()->get();

		$granularity = $this->bucket_computer->resolveGranularity($this->resolvePhotoTimeline($album));

		$bucket_ids = [];
		$counts = [];
		$unknown_count = null;
		foreach ($rows as $row) {
			$bucket_id = $this->liveBucketId($column, $granularity, $row);
			if ($bucket_id === null) {
				// Accumulated separately and always appended last (below),
				// regardless of $order - "unknown" rows need not even be
				// contiguous with each other in the sorted result.
				$unknown_count = ($unknown_count ?? 0) + 1;
				continue;
			}

			$last_index = count($bucket_ids) - 1;
			if ($last_index >= 0 && $bucket_ids[$last_index] === $bucket_id) {
				$counts[$last_index]++;
			} else {
				$bucket_ids[] = $bucket_id;
				$counts[] = 1;
			}
		}

		if ($unknown_count !== null) {
			$bucket_ids[] = 'unknown';
			$counts[] = $unknown_count;
		}

		return [$bucket_ids, $counts];
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
