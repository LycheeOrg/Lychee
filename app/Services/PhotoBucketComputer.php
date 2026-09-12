<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Enum\ColumnSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Repositories\ConfigManager;
use Carbon\Carbon;
use function Safe\mktime;

/**
 * Shared `bucket_id` truncation logic for the `photo_album` pivot row,
 * reused by every write path that populates it: the pivot-link-insert
 * trigger (upload, {@see \App\Actions\Photo\MoveOrDuplicate::do()}),
 * {@see \App\Jobs\RecomputePhotoBucketsJob},
 * {@see \App\Jobs\RecomputeAlbumPhotoBucketsJob}, and the
 * `lychee:recompute-buckets` backfill command — so a bucket-truncation
 * bugfix or a `photo_title_bucket_mode` semantics change only needs to
 * happen once.
 *
 * Direct structural precedent: {@see \App\Services\AlbumBucketComputer}.
 * Operates purely on already-resolved scalar/Carbon inputs — never queries
 * `photos`/`albums`/`size_variants`/`tags` itself; each caller resolves the
 * album's own effective sort column/granularity (which differs by write
 * path: an Eloquent relation for a single album, a raw self-join for a
 * full-table pass) and the photo's own bucket-relevant columns, handing this
 * class only the already-decided values.
 */
final class PhotoBucketComputer
{
	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Resolves `$candidate` (an album's own `photo_timeline` override, or
	 * `null`/`DEFAULT`/`DISABLED` for "use the instance-wide default")
	 * against the instance-wide `timeline_photos_granularity` default,
	 * mirroring {@see \App\Http\Resources\Traits\HasTimelineData::getPhotoTimeline()}
	 * — reimplemented here (rather than reusing that trait) because it reads
	 * config via `request()->configs()`, which is unavailable in a
	 * queue-worker/console-command context.
	 */
	public function resolveGranularity(?TimelinePhotoGranularity $candidate): TimelinePhotoGranularity
	{
		$default = $this->config_manager->getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class) ?? TimelinePhotoGranularity::YEAR;

		if ($candidate === TimelinePhotoGranularity::DEFAULT || $candidate === TimelinePhotoGranularity::DISABLED) {
			return $default;
		}

		return $candidate ?? $default;
	}

	/**
	 * Computes one `photo_album` row's `bucket_id`, given the photo's own
	 * `title`/`title_base`/`created_at`/`taken_at`/`is_highlighted`/`type`/
	 * `rating_avg` values and the containing album's already-resolved
	 * effective sort column and granularity.
	 *
	 * `OWNER_ID` is never a bucketable source for photos — excluded
	 * entirely, per explicit user direction — and always yields
	 * `null`, uncomputed, mirroring {@see AlbumBucketComputer::compute()}'s
	 * own `OWNER_ID` short-circuit (there for a structurally different
	 * reason: every direct child of one album always shares that album's
	 * exact owner_id, whereas for photos this is a deliberate policy choice
	 * even though ownership genuinely can vary photo-by-photo).
	 *
	 * @param string|null $rating_avg the photo's `rating_avg` column, as the raw
	 *                                decimal-cast string (or `null` if never rated)
	 */
	public function compute(
		ColumnSortingType $sorting_column,
		TimelinePhotoGranularity $granularity,
		string $title,
		string $title_base,
		Carbon $created_at,
		?Carbon $taken_at,
		bool $is_highlighted,
		string $type,
		?string $rating_avg,
	): ?string {
		if ($sorting_column === ColumnSortingType::OWNER_ID) {
			return null;
		}

		if ($sorting_column === ColumnSortingType::TITLE) {
			return $this->computeTitleBucket($granularity, $title, $title_base);
		}

		if ($sorting_column === ColumnSortingType::IS_HIGHLIGHTED) {
			return $is_highlighted ? '1' : '0';
		}

		if ($sorting_column === ColumnSortingType::TYPE) {
			return $type;
		}

		if ($sorting_column === ColumnSortingType::RATING_AVG) {
			// Every individual rating is constrained to 1-5, so a non-null
			// average can never round to "0" - the only way to reach the
			// "unknown" bucket for this column is a null (never-rated) average.
			return $rating_avg === null ? null : (string) round((float) $rating_avg);
		}

		$date = match ($sorting_column) {
			ColumnSortingType::CREATED_AT => $created_at,
			ColumnSortingType::TAKEN_AT => $taken_at,
			default => null,
		};

		return $date === null ? null : $this->truncateDate($date, $granularity);
	}

	/**
	 * Turns a `bucket_id` string (the exact `"Y"`/`"Y-m"`/`"Y-m-d"`/
	 * `"Y-m-d-H"` dash-separated truncation format {@see self::truncateDate()}/
	 * {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoSource::truncateRawDate()}
	 * both write) into the half-open `[start, end)` date-time range it
	 * covers, as `"Y-m-d H:i:s"` strings directly comparable against a raw
	 * `created_at`/`taken_at` column value — used to SQL-pushdown-bound a
	 * live, non-materialized `TimelineAlbum` bucket query (NFR-066-01)
	 * instead of a full-library PHP row scan.
	 *
	 * Deliberately Carbon/DateTime-free (`[[feedback_avoid_carbon_server_side]]`):
	 * `$end` is computed by handing `mktime()` one out-of-range component
	 * past `$start` (e.g. month 13, or day 32) and letting it normalize the
	 * overflow itself — the same well-established PHP idiom
	 * {@see \App\Actions\Photo\StructOfArrays\QueryPhotoBuckets::formatBucketLabel()}
	 * already uses `mktime()` for, applied here to interval arithmetic
	 * instead of formatting.
	 *
	 * Caller contract: `$bucket_id` must not be the `"unknown"` sentinel —
	 * that maps to `NULL` on the sort column, which has no date range and
	 * must be filtered with `whereNull()` instead, by the caller, before
	 * ever reaching here.
	 *
	 * @param string $bucket_id a real (non-`"unknown"`) bucket id
	 *
	 * @return array{0:string,1:string} `[start, end)`, both `"Y-m-d H:i:s"`
	 */
	public function bucketDateRange(string $bucket_id, TimelinePhotoGranularity $granularity): array
	{
		$parts = explode('-', $bucket_id);
		$year = (int) $parts[0];
		$month = (int) ($parts[1] ?? 1);
		$day = (int) ($parts[2] ?? 1);
		$hour = (int) ($parts[3] ?? 0);

		return match ($granularity) {
			TimelinePhotoGranularity::YEAR => [
				date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year)),
				date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year + 1)),
			],
			TimelinePhotoGranularity::MONTH => [
				date('Y-m-d H:i:s', mktime(0, 0, 0, $month, 1, $year)),
				date('Y-m-d H:i:s', mktime(0, 0, 0, $month + 1, 1, $year)),
			],
			TimelinePhotoGranularity::DAY => [
				date('Y-m-d H:i:s', mktime(0, 0, 0, $month, $day, $year)),
				date('Y-m-d H:i:s', mktime(0, 0, 0, $month, $day + 1, $year)),
			],
			TimelinePhotoGranularity::HOUR => [
				date('Y-m-d H:i:s', mktime($hour, 0, 0, $month, $day, $year)),
				date('Y-m-d H:i:s', mktime($hour + 1, 0, 0, $month, $day, $year)),
			],
			// @codeCoverageIgnoreStart
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid resolved granularity for photos'),
			// @codeCoverageIgnoreEnd
		};
	}

	/**
	 * Truncates a raw, un-cast `photos.created_at`/`photos.taken_at`
	 * datetime string (`"Y-m-d H:i:s[.u]"`, the exact form every supported
	 * DB driver's PDO layer returns for a `toBase()`/`DB::table()` row) at
	 * `$granularity`, in the same dash-separated format
	 * {@see self::truncateDate()} writes — via plain substring slicing,
	 * deliberately never a `Carbon`/`DateTime` object
	 * (`[[feedback_avoid_carbon_server_side]]`).
	 *
	 * Byte-for-byte duplicate of
	 * {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoSource::truncateRawDate()}
	 * (a `private` trait method, not reachable from outside the v3
	 * photo-tier query classes) - kept as a small, deliberate duplication
	 * rather than a cross-cutting refactor of that already-shipped,
	 * tested trait, for a second caller
	 * ({@see \App\Listeners\ManagedCachePhotoListingInvalidator}) outside
	 * the request-path query stack.
	 */
	public function truncateRawDate(string $raw_date, TimelinePhotoGranularity $granularity): string
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

	/**
	 * Bucket computation for a `TITLE`-sorted album, branching on the
	 * instance-wide, **photo-specific** `photo_title_bucket_mode` config —
	 * never the album-only `title_bucket_mode`.
	 */
	private function computeTitleBucket(TimelinePhotoGranularity $granularity, string $title, string $title_base): ?string
	{
		$mode = $this->config_manager->getValueAsEnum('photo_title_bucket_mode', TitleBucketMode::class) ?? TitleBucketMode::DATE_PREFIX;

		if ($mode === TitleBucketMode::ALPHABETICAL) {
			$length = max(1, $this->config_manager->getValueAsInt('photo_title_bucket_prefix_length'));

			return mb_substr($title_base, 0, $length);
		}

		$date = TimelineData::parseDateFromTitle(trim($title));
		if ($date === null) {
			return null;
		}

		return $this->truncateDate($date, $granularity);
	}

	/**
	 * Truncates `$date` at `$granularity`, in the same `Y`/`Y-m`/`Y-m-d`
	 * format {@see AlbumBucketComputer::truncateDate()} uses for the three
	 * granularities photos share with albums, extended with a 4th,
	 * photos-only `Y-m-d-H` tier for {@see TimelinePhotoGranularity::HOUR} —
	 * dash-separated throughout so the buckets endpoint's label formatter
	 * can re-parse every tier identically via a single `explode('-', ...)`.
	 */
	private function truncateDate(Carbon $date, TimelinePhotoGranularity $granularity): string
	{
		return match ($granularity) {
			TimelinePhotoGranularity::YEAR => $date->format('Y'),
			TimelinePhotoGranularity::MONTH => $date->format('Y-m'),
			TimelinePhotoGranularity::DAY => $date->format('Y-m-d'),
			TimelinePhotoGranularity::HOUR => $date->format('Y-m-d-H'),
			// @codeCoverageIgnoreStart
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid resolved granularity for photos'),
			// @codeCoverageIgnoreEnd
		};
	}
}
