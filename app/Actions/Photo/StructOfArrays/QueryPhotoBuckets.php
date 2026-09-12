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
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Http\Resources\V3\PhotoBucketResource;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\User;
use App\Services\PhotoBucketComputer;
use App\SmartAlbums\TimelineAlbum;
use Illuminate\Support\Facades\DB;
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
		} elseif ($album instanceof TimelineAlbum) {
			// Timeline's scope is potentially the whole library - unlike
			// TagAlbum/PersonAlbum (proven safe at album scale), a PHP row
			// scan here would not be SQL-pushdown bounded (NFR-066-01).
			// $sorting->column is always CREATED_AT or TAKEN_AT for a
			// TimelineAlbum source (see ResolvesPhotoSource::resolveEffectiveSorting()),
			// so a driver-specific `GROUP BY` truncation on that raw date
			// column is sufficient - no OWNER_ID/TITLE/IS_HIGHLIGHTED/TYPE/
			// RATING_AVG case to handle here.
			$granularity = $this->bucket_computer->resolveGranularity($this->resolvePhotoTimeline($album));
			[$bucket_ids, $counts] = $this->queryPushdownBuckets($album, $user, $sorting->column, $sorting->order, $granularity);
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
	 * SQL-pushdown-bounded `GROUP BY` truncation, for a {@see TimelineAlbum}
	 * source only (NFR-066-01) - cost is bounded by the distinct-bucket
	 * count the `GROUP BY` yields, never by candidate-photo count, unlike
	 * {@see self::queryLiveBuckets()}'s PHP row scan (kept unchanged for
	 * `TagAlbum`/`PersonAlbum`, proven safe only at album scale).
	 *
	 * Every driver truncates to the same dash-separated `"Y"`/`"Y-m"`/
	 * `"Y-m-d"`/`"Y-m-d-H"` format {@see PhotoBucketComputer::truncateDate()}/
	 * {@see ResolvesPhotoSource::truncateRawDate()} both write — required so
	 * a `bucket_id` returned here agrees byte-for-byte with the same
	 * photo's `bucket_id` as computed live by the `ratios`/`details` tiers
	 * for the same `TimelineAlbum` source. This is a deliberate departure
	 * from {@see \App\Actions\Photo\Timeline::dates()}'s own per-driver
	 * `HOUR` format (`...T HH24`, a `T`-separated ISO string never meant to
	 * be compared against another tier's bucket id) - that v2-only method
	 * is intentionally left untouched (FR-066-15), not reused here.
	 *
	 * `NULL`s ("unknown") are counted via one extra, cheap, unfiltered
	 * `COUNT(*)` rather than folded into the `GROUP BY` itself (`GROUP BY`
	 * treats every `NULL` as one single group already, so this would work
	 * too — kept as a separate query purely so "unknown" can be appended
	 * last unconditionally, mirroring {@see self::queryLiveBuckets()}'s own
	 * always-last placement regardless of `$order`).
	 *
	 * @return array{0:string[],1:int[]}
	 */
	private function queryPushdownBuckets(AbstractAlbum $album, ?User $user, ColumnSortingType $column, OrderSortingType $order, TimelinePhotoGranularity $granularity): array
	{
		$direction = $order === OrderSortingType::DESC ? 'desc' : 'asc';
		$date_expression = self::dateTruncationExpression($column->value, $granularity);

		$rows = $this->resolvePhotoQuery($album, $user)
			->whereNotNull($column->value)
			->selectRaw($date_expression . ' as bucket_id')
			->selectRaw('COUNT(*) as bucket_count')
			->groupBy('bucket_id')
			->orderBy('bucket_id', $direction)
			->toBase()
			->get();

		$bucket_ids = [];
		$counts = [];
		foreach ($rows as $row) {
			$bucket_ids[] = (string) $row->bucket_id;
			$counts[] = (int) $row->bucket_count;
		}

		$unknown_count = $this->resolvePhotoQuery($album, $user)->whereNull($column->value)->count();
		if ($unknown_count > 0) {
			$bucket_ids[] = 'unknown';
			$counts[] = $unknown_count;
		}

		return [$bucket_ids, $counts];
	}

	/**
	 * Driver-specific SQL expression truncating `$column` to `$granularity`,
	 * in the dash-separated `"Y"`/`"Y-m"`/`"Y-m-d"`/`"Y-m-d-H"` format - see
	 * {@see self::queryPushdownBuckets()}'s docblock for why this must match
	 * that format exactly, rather than {@see \App\Actions\Photo\Timeline::dates()}'s
	 * own per-driver formatting.
	 */
	private static function dateTruncationExpression(string $column, TimelinePhotoGranularity $granularity): string
	{
		$is_driver_pgsql = DB::getDriverName() === 'pgsql';

		$formatter = match (DB::getDriverName()) {
			'sqlite' => 'strftime(\'%2$s\', %1$s)',
			'mysql', 'mariadb' => 'DATE_FORMAT(%1$s, \'%2$s\')',
			'pgsql' => 'to_char(%1$s, \'%2$s\')',
			default => throw new LycheeInvalidArgumentException('Unsupported database driver'),
		};

		$date_format = match ($granularity) {
			TimelinePhotoGranularity::YEAR => $is_driver_pgsql ? 'YYYY' : '%Y',
			TimelinePhotoGranularity::MONTH => $is_driver_pgsql ? 'YYYY-MM' : '%Y-%m',
			TimelinePhotoGranularity::DAY => $is_driver_pgsql ? 'YYYY-MM-DD' : '%Y-%m-%d',
			TimelinePhotoGranularity::HOUR => $is_driver_pgsql ? 'YYYY-MM-DD-HH24' : '%Y-%m-%d-%H',
			// @codeCoverageIgnoreStart
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeInvalidArgumentException('DEFAULT/DISABLED is not a valid resolved granularity for photos'),
			// @codeCoverageIgnoreEnd
		};

		return sprintf($formatter, $column, $date_format);
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
