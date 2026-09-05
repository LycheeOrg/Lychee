<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Enum\ColumnSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TitleBucketMode;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Repositories\ConfigManager;
use Carbon\Carbon;

/**
 * Shared `bucket_id` truncation logic, reused by every write path that populates it:
 * {@see \App\Jobs\RecomputeAlbumStatsJob}, {@see \App\Jobs\RecomputeChildAlbumBucketsJob},
 * and the `lychee:recompute-album-buckets` backfill command — so a
 * bucket-truncation bugfix or a `title_bucket_mode` semantics change only
 * needs to happen once.
 *
 * Operates purely on already-resolved scalar/Carbon inputs — never queries
 * `photos` and never resolves an `Album`'s parent itself; each
 * caller is responsible for resolving the effective sort column/granularity
 * (which differs by write path: an Eloquent relation for a single album, a
 * raw self-join for a full-table pass) and handing this class only the
 * already-decided values.
 */
final class AlbumBucketComputer
{
	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Resolves `$candidate` (a parent's own `album_timeline` override, or
	 * `null` for a root album/no override) against the instance-wide
	 * `timeline_albums_granularity` default, mirroring
	 * {@see \App\Http\Resources\Traits\HasTimelineData::getAlbumTimeline()}
	 * — reimplemented here (rather than reusing that trait) because it reads
	 * config via `request()->configs()`, which is unavailable in a
	 * queue-worker/console-command context.
	 */
	public function resolveGranularity(?TimelineAlbumGranularity $candidate): TimelineAlbumGranularity
	{
		$default = $this->config_manager->getValueAsEnum('timeline_albums_granularity', TimelineAlbumGranularity::class) ?? TimelineAlbumGranularity::YEAR;

		if ($candidate === TimelineAlbumGranularity::DEFAULT || $candidate === TimelineAlbumGranularity::DISABLED) {
			return $default;
		}

		return $candidate ?? $default;
	}

	/**
	 * Computes one album row's `bucket_id`, given its own `title`/
	 * `title_base`/`created_at`/`min_taken_at`/`max_taken_at` values and its
	 * parent's already-resolved effective sort column and granularity.
	 * `OWNER_ID` is not a bucketable source (every direct child of a given
	 * album always shares that album's exact owner_id) and always yields
	 * `null`, uncomputed.
	 */
	public function compute(
		ColumnSortingType $sorting_column,
		TimelineAlbumGranularity $granularity,
		string $title,
		string $title_base,
		Carbon $created_at,
		?Carbon $min_taken_at,
		?Carbon $max_taken_at,
	): ?string {
		if ($sorting_column === ColumnSortingType::OWNER_ID) {
			return null;
		}

		if ($sorting_column === ColumnSortingType::TITLE) {
			return $this->computeTitleBucket($granularity, $title, $title_base);
		}

		$date = match ($sorting_column) {
			ColumnSortingType::CREATED_AT => $created_at,
			ColumnSortingType::MIN_TAKEN_AT => $min_taken_at,
			ColumnSortingType::MAX_TAKEN_AT => $max_taken_at,
			default => null,
		};

		return $date === null ? null : $this->truncateDate($date, $granularity);
	}

	/**
	 * Bucket computation for a `TITLE`-sorted parent, branching on the
	 * instance-wide `title_bucket_mode` config.
	 */
	private function computeTitleBucket(TimelineAlbumGranularity $granularity, string $title, string $title_base): ?string
	{
		$mode = $this->config_manager->getValueAsEnum('title_bucket_mode', TitleBucketMode::class) ?? TitleBucketMode::DATE_PREFIX;

		if ($mode === TitleBucketMode::ALPHABETICAL) {
			$length = max(1, $this->config_manager->getValueAsInt('title_bucket_prefix_length'));

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
	 * format {@see TimelineData::fromAlbum()}'s `time_date` uses, so the
	 * buckets endpoint can re-parse `bucket_id` as a real date at read time.
	 */
	private function truncateDate(Carbon $date, TimelineAlbumGranularity $granularity): string
	{
		return match ($granularity) {
			TimelineAlbumGranularity::YEAR => $date->format('Y'),
			TimelineAlbumGranularity::MONTH => $date->format('Y-m'),
			TimelineAlbumGranularity::DAY => $date->format('Y-m-d'),
			// @codeCoverageIgnoreStart
			TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid resolved granularity for albums'),
			// @codeCoverageIgnoreEnd
		};
	}
}
