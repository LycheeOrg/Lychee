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
use App\Models\Album;
use App\Repositories\ConfigManager;

/**
 * `albums.bucket_key` of one album for its parent's effective album sort
 * column (FR-092-04, ADR-092-01). Album dates are aggregate instants
 * stored and displayed without a zone: their keys are the stored (UTC)
 * day. The key never depends on the granularity or the title prefix
 * length.
 *
 * Shared by {@see \App\Jobs\RecomputeAlbumStatsJob} and, through
 * {@see BucketKeyWriter}, {@see \App\Jobs\RecomputeChildAlbumBucketsJob},
 * {@see \App\Jobs\RecomputeAllAlbumBucketsJob} and `lychee:recompute-buckets`.
 */
final class AlbumBucketComputer
{
	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Resolves `$candidate` (a parent's `album_timeline` override, or `null`
	 * for a root album) against the instance-wide
	 * `timeline_albums_granularity` default.
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
	 * Read-time scheme of a children listing sorted by `$column`, with the
	 * parent's `album_timeline` override (`null` for root).
	 */
	public function scheme(ColumnSortingType $column, ?TimelineAlbumGranularity $candidate): BucketScheme
	{
		return BucketScheme::forAlbums($column, $this->resolveGranularity($candidate), $this->config_manager);
	}

	/**
	 * Key of a raw album row (`base_albums` joined with `albums`).
	 *
	 * @param object{title:string,title_base:?string,created_at:string,min_taken_at:?string,max_taken_at:?string} $row
	 */
	public function keyForRow(ColumnSortingType $column, object $row): ?string
	{
		return $this->key(
			$column,
			$row->title,
			$row->title_base,
			substr((string) $row->created_at, 0, 10),
			$row->min_taken_at === null ? null : substr((string) $row->min_taken_at, 0, 10),
			$row->max_taken_at === null ? null : substr((string) $row->max_taken_at, 0, 10),
		);
	}

	public function keyForAlbum(ColumnSortingType $column, Album $album): ?string
	{
		return $this->key(
			$column,
			$album->title,
			$album->title_base,
			BucketKeys::storedDayKey($album->created_at) ?? '',
			BucketKeys::storedDayKey($album->min_taken_at),
			BucketKeys::storedDayKey($album->max_taken_at),
		);
	}

	/**
	 * `OWNER_ID` yields `null`: every child of one album shares its owner.
	 *
	 * @param string      $created_day   `created_at` as stored, `Y-m-d`
	 * @param string|null $min_taken_day `min_taken_at` as stored, `Y-m-d`
	 * @param string|null $max_taken_day `max_taken_at` as stored, `Y-m-d`
	 */
	public function key(
		ColumnSortingType $column,
		string $title,
		?string $title_base,
		string $created_day,
		?string $min_taken_day,
		?string $max_taken_day,
	): ?string {
		return match ($column) {
			ColumnSortingType::CREATED_AT => $created_day,
			ColumnSortingType::MIN_TAKEN_AT => $min_taken_day,
			ColumnSortingType::MAX_TAKEN_AT => $max_taken_day,
			ColumnSortingType::TITLE => $this->isAlphabetical() ? mb_substr($title_base ?? '', 0, BucketScheme::MAX_PREFIX_LENGTH) : BucketKeys::titleDateKey($title),
			default => null,
		};
	}

	private function isAlphabetical(): bool
	{
		return $this->config_manager->getValueAsEnum('title_bucket_mode', TitleBucketMode::class) === TitleBucketMode::ALPHABETICAL;
	}
}
