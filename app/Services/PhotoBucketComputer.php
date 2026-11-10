<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Assets\DbBool;
use App\Enum\ColumnSortingType;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Models\Photo;
use App\Repositories\ConfigManager;

/**
 * `photo_album.bucket_key` of one photo for its containing album's
 * effective sort column (FR-092-03, ADR-092-01). The key never depends on
 * the granularity or the title prefix length: {@see BucketScheme} derives
 * the bucket id from it at read time.
 *
 * Shared by every write path: the pivot insert of an upload or a
 * move/copy, {@see \App\Jobs\RecomputePhotoBucketsJob},
 * {@see \App\Jobs\RecomputeAlbumPhotoBucketsJob},
 * {@see \App\Jobs\RecomputeAllPhotoBucketsJob} and
 * `lychee:recompute-buckets` (through {@see BucketKeyWriter}).
 */
final class PhotoBucketComputer
{
	/** Columns of `photos` a raw row needs for {@see self::keyForRow()}. */
	public const ROW_COLUMNS = ['taken_key', 'created_at', 'title_date_key', 'title_base', 'is_highlighted', 'type', 'rating_avg'];

	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Resolves `$candidate` (an album's `photo_timeline` override, or `null`)
	 * against the instance-wide `timeline_photos_granularity` default.
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
	 * Read-time scheme of a photo listing sorted by `$column`, with the
	 * album's `photo_timeline` override (`null` for none).
	 */
	public function scheme(ColumnSortingType $column, ?TimelinePhotoGranularity $candidate): BucketScheme
	{
		return BucketScheme::forPhotos($column, $this->resolveGranularity($candidate), $this->config_manager);
	}

	/**
	 * Key of a raw `photos` row selected with {@see self::ROW_COLUMNS}.
	 *
	 * @param object{taken_key:?string,created_at:string,title_date_key:?string,title_base:?string,is_highlighted:mixed,type:?string,rating_avg:mixed} $row
	 */
	public function keyForRow(ColumnSortingType $column, object $row): ?string
	{
		return match ($column) {
			ColumnSortingType::CREATED_AT => BucketKeys::createdKey((string) $row->created_at),
			default => $this->key($column, $row->taken_key, '', $row->title_date_key, $row->title_base, DbBool::parse($row->is_highlighted), $row->type, $row->rating_avg),
		};
	}

	/**
	 * Key of a loaded photo, whose `taken_key`/`title_date_key` are already
	 * assigned.
	 */
	public function keyForPhoto(ColumnSortingType $column, Photo $photo): ?string
	{
		return match ($column) {
			ColumnSortingType::CREATED_AT => BucketKeys::storedHourKey($photo->created_at),
			default => $this->key($column, $photo->taken_key, '', $photo->title_date_key, $photo->title_base, $photo->is_highlighted, $photo->type, $photo->rating_avg),
		};
	}

	/**
	 * `OWNER_ID` is not bucketable for photos and yields `null`.
	 *
	 * @param string $created_key `created_at` as stored, `Y-m-d-H`
	 */
	public function key(
		ColumnSortingType $column,
		?string $taken_key,
		string $created_key,
		?string $title_date_key,
		?string $title_base,
		bool $is_highlighted,
		?string $type,
		int|float|string|null $rating_avg,
	): ?string {
		return match ($column) {
			ColumnSortingType::TAKEN_AT => $taken_key,
			ColumnSortingType::CREATED_AT => $created_key,
			ColumnSortingType::TITLE => $this->isAlphabetical() ? mb_substr($title_base ?? '', 0, BucketScheme::MAX_PREFIX_LENGTH) : $title_date_key,
			ColumnSortingType::IS_HIGHLIGHTED => $is_highlighted ? '1' : '0',
			ColumnSortingType::TYPE => $type ?? '',
			// Every rating is 1-5, so only a never-rated photo reaches `unknown`.
			ColumnSortingType::RATING_AVG => $rating_avg === null ? null : (string) (int) round((float) $rating_avg),
			default => null,
		};
	}

	private function isAlphabetical(): bool
	{
		return $this->config_manager->getValueAsEnum('photo_title_bucket_mode', TitleBucketMode::class) === TitleBucketMode::ALPHABETICAL;
	}
}
