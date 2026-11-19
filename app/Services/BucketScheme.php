<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Assets\DbBool;
use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Enum\TitleBucketMode;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use function Safe\gmmktime;
use function Safe\preg_match;

/**
 * Read-time bucket ids (ADR-092-01): one scheme per listing request,
 * derived from the effective sort column, the resolved granularity and the
 * title bucket settings.
 *
 * A stored key (`photo_album.bucket_key`, `albums.bucket_key`) is never
 * granularity-specific: the bucket id is a SQL prefix of it. Sources
 * without a stored key (tag, person, smart albums, Timeline) compute the
 * same id in SQL from photo columns.
 *
 * Column names and lengths are interpolated into SQL; they come from this
 * class only, never from a request.
 */
final class BucketScheme
{
	public const UNKNOWN = 'unknown';

	/** Width of `bucket_key`: the longest alphabetical key stored. */
	public const MAX_PREFIX_LENGTH = 255;

	/** Fixed-width date key, ids are prefixes and windows key ranges. */
	private const DATE = 'date';
	/** Raw `photos.created_at` (sources without stored key): ids from the driver's date formatting, windows datetime ranges. */
	private const CREATED = 'created';
	/** Alphabetical title: ids are the first characters. */
	private const PREFIX = 'prefix';
	/** `is_highlighted`: ids `1`/`0`. */
	private const FLAG = 'flag';
	/** `type`: the value itself. */
	private const VALUE = 'value';
	/** `rating_avg`: the rounded average. */
	private const RATING = 'rating';
	/** Not bucketable (`OWNER_ID`). */
	private const NONE = 'none';

	private const DATE_LENGTHS = [4, 7, 10, 13];
	private const TITLE_DATE_LENGTH = 10;
	/** Shape of a date id: `Y`, `Y-m`, `Y-m-d` or `Y-m-d-H`. */
	private const DATE_ID_PATTERN = '/^\d{4}(?:-\d{2}){0,3}$/';

	/**
	 * @param string   $kind          one of the private kind constants
	 * @param int|null $length        id prefix length, `null` for whole values
	 * @param string   $source_column photo column the id derives from on sources without stored key, `''` for albums
	 * @param string[] $date_formats  label formats by id precision: year, month, day, hour (same day), then the multi-day hour format (FR-095-08)
	 */
	private function __construct(
		private readonly string $kind,
		private readonly ?int $length,
		private readonly string $source_column,
		private readonly array $date_formats,
	) {
	}

	public static function forPhotos(ColumnSortingType $column, TimelinePhotoGranularity $granularity, ConfigManager $config): self
	{
		$formats = [
			$config->getValueAsString('timeline_photo_date_format_year'),
			$config->getValueAsString('timeline_photo_date_format_month'),
			$config->getValueAsString('timeline_photo_date_format_day'),
			$config->getValueAsString('timeline_photo_date_format_hour'),
			$config->getValueAsString('timeline_photo_date_format_hour_multi_day'),
		];

		return match ($column) {
			ColumnSortingType::TAKEN_AT => new self(self::DATE, self::photoDateLength($granularity), 'photos.taken_key', $formats),
			ColumnSortingType::CREATED_AT => new self(self::CREATED, self::photoDateLength($granularity), 'photos.created_at', $formats),
			ColumnSortingType::TITLE => self::isAlphabetical($config, 'photo_title_bucket_mode')
				? new self(self::PREFIX, self::prefixLength($config, 'photo_title_bucket_prefix_length'), 'photos.title_base', $formats)
				: new self(self::DATE, min(self::photoDateLength($granularity), self::TITLE_DATE_LENGTH), 'photos.title_date_key', $formats),
			ColumnSortingType::IS_HIGHLIGHTED => new self(self::FLAG, null, 'photos.is_highlighted', $formats),
			ColumnSortingType::TYPE => new self(self::VALUE, null, 'photos.type', $formats),
			ColumnSortingType::RATING_AVG => new self(self::RATING, null, 'photos.rating_avg', $formats),
			default => new self(self::NONE, null, '', $formats),
		};
	}

	public static function forAlbums(ColumnSortingType $column, TimelineAlbumGranularity $granularity, ConfigManager $config): self
	{
		$formats = [
			$config->getValueAsString('timeline_album_date_format_year'),
			$config->getValueAsString('timeline_album_date_format_month'),
			$config->getValueAsString('timeline_album_date_format_day'),
		];

		return match ($column) {
			ColumnSortingType::CREATED_AT,
			ColumnSortingType::MIN_TAKEN_AT,
			ColumnSortingType::MAX_TAKEN_AT => new self(self::DATE, self::albumDateLength($granularity), '', $formats),
			ColumnSortingType::TITLE => self::isAlphabetical($config, 'title_bucket_mode')
				? new self(self::PREFIX, self::prefixLength($config, 'title_bucket_prefix_length'), '', $formats)
				: new self(self::DATE, self::albumDateLength($granularity), '', $formats),
			default => new self(self::NONE, null, '', $formats),
		};
	}

	public function isBucketable(): bool
	{
		return $this->kind !== self::NONE;
	}

	/**
	 * SQL bucket id of a stored key column.
	 */
	public function storedIdSql(string $key_column): string
	{
		return $this->length === null ? $key_column : 'SUBSTR(' . $key_column . ', 1, ' . $this->length . ')';
	}

	/**
	 * Bucket id of a key computed in PHP (a stored key, or for `CREATED_AT`
	 * the `Y-m-d-H` key of the raw column), the same value
	 * {@see self::storedIdSql()} yields in SQL.
	 */
	public function idOfKey(?string $key): string
	{
		if ($key === null) {
			return self::UNKNOWN;
		}

		return $this->length === null ? $this->normalizeId($key) : mb_substr($key, 0, $this->length);
	}

	/**
	 * SQL bucket id computed from photo columns, for sources without stored key.
	 */
	public function sourceIdSql(): string
	{
		return match ($this->kind) {
			self::DATE, self::PREFIX => $this->storedIdSql($this->sourceColumn()),
			self::CREATED => self::createdIdSql($this->sourceColumn(), $this->length ?? 13),
			self::RATING => 'ROUND(' . $this->sourceColumn() . ')',
			self::FLAG, self::VALUE => $this->sourceColumn(),
			// @codeCoverageIgnoreStart
			default => throw new LycheeLogicException('Bucket scheme has no source column'),
			// @codeCoverageIgnoreEnd
		};
	}

	/**
	 * Photo column the bucket id derives from, for sources without stored key.
	 */
	public function sourceColumn(): string
	{
		if ($this->source_column === '') {
			// @codeCoverageIgnoreStart
			throw new LycheeLogicException('Bucket scheme has no source column');
			// @codeCoverageIgnoreEnd
		}

		return $this->source_column;
	}

	/**
	 * Nested `where` restricting a stored key column to `$bucket_ids`.
	 *
	 * @param string[] $bucket_ids
	 *
	 * @return \Closure(mixed):void
	 */
	public function storedWindow(string $key_column, array $bucket_ids): \Closure
	{
		return function ($query) use ($key_column, $bucket_ids): void {
			foreach ($bucket_ids as $bucket_id) {
				$query->orWhere(fn ($q) => $this->whereStoredId($q, $key_column, $bucket_id));
			}
		};
	}

	/**
	 * Nested `where` restricting photo columns to `$bucket_ids`, for sources
	 * without stored key.
	 *
	 * @param string[] $bucket_ids
	 *
	 * @return \Closure(mixed):void
	 */
	public function sourceWindow(array $bucket_ids): \Closure
	{
		return function ($query) use ($bucket_ids): void {
			foreach ($bucket_ids as $bucket_id) {
				$query->orWhere(fn ($q) => $this->whereSourceId($q, $bucket_id));
			}
		};
	}

	/**
	 * Orders rows by bucket: `unknown` last, then the id in `$order`.
	 *
	 * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
	 */
	public function orderRows($query, string $id_sql, OrderSortingType $order): void
	{
		$query->orderByRaw('(' . $id_sql . ' IS NULL) ASC')
			->orderByRaw($id_sql . ' ' . self::direction($order));
	}

	/**
	 * Turns grouped rows (`bucket_id`, `bucket_count`), ordered by id, into
	 * `[bucket_ids, counts]` with `unknown` last.
	 *
	 * @param iterable<object{bucket_id:mixed,bucket_count:int|string}> $rows
	 *
	 * @return array{0:string[],1:int[]}
	 */
	public function collectBuckets(iterable $rows): array
	{
		$bucket_ids = [];
		$counts = [];
		$unknown_count = null;
		foreach ($rows as $row) {
			if ($row->bucket_id === null) {
				$unknown_count = ($unknown_count ?? 0) + (int) $row->bucket_count;
				continue;
			}
			$bucket_ids[] = $this->normalizeId($row->bucket_id);
			$counts[] = (int) $row->bucket_count;
		}

		if ($unknown_count !== null) {
			$bucket_ids[] = self::UNKNOWN;
			$counts[] = $unknown_count;
		}

		return [$bucket_ids, $counts];
	}

	/**
	 * Bucket id of a value read from SQL (`NULL` → `unknown`).
	 */
	public function normalizeId(mixed $value): string
	{
		if ($value === null) {
			return self::UNKNOWN;
		}

		return match ($this->kind) {
			self::FLAG => DbBool::parse($value) ? '1' : '0',
			self::RATING => (string) (int) round((float) $value),
			default => (string) $value,
		};
	}

	/**
	 * @param string[] $bucket_ids
	 *
	 * @return string[]
	 */
	public function labels(array $bucket_ids): array
	{
		$hour_format = self::spansSeveralDays($bucket_ids) ? ($this->date_formats[4] ?? null) : null;

		return array_map(fn (string $bucket_id): string => $this->label($bucket_id, $hour_format), $bucket_ids);
	}

	/**
	 * FR-095-08: whether the hour ids of a listing fall on more than one day.
	 * Their headers then need the date; on a single day the hour alone is
	 * unambiguous.
	 *
	 * @param string[] $bucket_ids
	 */
	private static function spansSeveralDays(array $bucket_ids): bool
	{
		$days = [];
		foreach ($bucket_ids as $bucket_id) {
			if (self::isDateId($bucket_id) && substr_count($bucket_id, '-') === 3) {
				$days[substr($bucket_id, 0, 10)] = true;
			}
		}

		return count($days) > 1;
	}

	/**
	 * Label of one id: date ids are formatted by their own precision (year,
	 * month, day, hour); other ids, `unknown` and ids of a date scheme that
	 * are not date-shaped (keys written for another scheme, FR-092-13) are
	 * returned as is. Hour ids use `$hour_format` when given (the multi-day
	 * format, FR-095-08).
	 */
	public function label(string $bucket_id, ?string $hour_format = null): string
	{
		if (($this->kind !== self::DATE && $this->kind !== self::CREATED) || !self::isDateId($bucket_id)) {
			return $bucket_id;
		}

		[$year, $month, $day, $hour, $precision] = self::dateParts($bucket_id);
		$format = $precision === 4 && $hour_format !== null ? $hour_format : $this->date_formats[min($precision, 4, count($this->date_formats)) - 1];

		return gmdate($format, gmmktime($hour, 0, 0, $month, $day, $year));
	}

	/**
	 * Smallest id of the same precision after `$bucket_id`: the exclusive
	 * end of its key range. Calendar arithmetic in UTC, since keys are
	 * wall-clock values of any zone.
	 */
	public static function nextDateId(string $bucket_id): string
	{
		[$year, $month, $day, $hour, $precision] = self::dateParts($bucket_id);

		return match ($precision) {
			1 => sprintf('%04d', $year + 1),
			2 => gmdate('Y-m', gmmktime(0, 0, 0, $month + 1, 1, $year)),
			3 => gmdate('Y-m-d', gmmktime(0, 0, 0, $month, $day + 1, $year)),
			default => gmdate(BucketKeys::HOUR_KEY_FORMAT, gmmktime($hour + 1, 0, 0, $month, $day, $year)),
		};
	}

	/**
	 * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
	 */
	private function whereStoredId($query, string $key_column, string $bucket_id): void
	{
		if ($bucket_id === self::UNKNOWN) {
			$query->whereNull($key_column);

			return;
		}

		match ($this->kind) {
			self::DATE, self::CREATED => $query->where($key_column, '>=', $bucket_id)->where($key_column, '<', self::nextDateId($bucket_id)),
			self::PREFIX => $query->whereRaw($this->storedIdSql($key_column) . ' = ?', [$bucket_id]),
			default => $query->where($key_column, '=', $bucket_id),
		};
	}

	/**
	 * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
	 */
	private function whereSourceId($query, string $bucket_id): void
	{
		$column = $this->sourceColumn();
		if ($bucket_id === self::UNKNOWN) {
			$query->whereNull($column);

			return;
		}

		match ($this->kind) {
			self::CREATED => $query->where($column, '>=', self::startDateTime($bucket_id))->where($column, '<', self::startDateTime(self::nextDateId($bucket_id))),
			self::FLAG => $query->where($column, '=', $bucket_id === '1'),
			self::RATING => $query->where($column, '>=', (int) $bucket_id - 0.5)->where($column, '<', (int) $bucket_id + 0.5),
			default => $this->whereStoredId($query, $column, $bucket_id),
		};
	}

	private static function isDateId(string $bucket_id): bool
	{
		return preg_match(self::DATE_ID_PATTERN, $bucket_id) === 1;
	}

	/**
	 * `[year, month, day, hour, precision]` of a date id (`Y`, `Y-m`,
	 * `Y-m-d` or `Y-m-d-H`); missing parts read as the start of the period.
	 *
	 * @return array{0:int,1:int,2:int,3:int,4:int}
	 */
	private static function dateParts(string $bucket_id): array
	{
		$parts = explode('-', $bucket_id);

		return [(int) $parts[0], (int) ($parts[1] ?? 1), (int) ($parts[2] ?? 1), (int) ($parts[3] ?? 0), count($parts)];
	}

	/**
	 * First instant of a date id as a stored datetime (`Y-m-d H:i:s`).
	 */
	private static function startDateTime(string $bucket_id): string
	{
		[$year, $month, $day, $hour] = self::dateParts($bucket_id);

		return gmdate('Y-m-d H:i:s', gmmktime($hour, 0, 0, $month, $day, $year));
	}

	/**
	 * Driver-specific formatting of a raw datetime column to the first
	 * `$length` characters of a `Y-m-d-H` key.
	 */
	private static function createdIdSql(string $column, int $length): string
	{
		$driver = DB::getDriverName();
		$formats = match ($driver) {
			'pgsql' => ['YYYY', 'YYYY-MM', 'YYYY-MM-DD', 'YYYY-MM-DD-HH24'],
			'sqlite', 'mysql', 'mariadb' => ['%Y', '%Y-%m', '%Y-%m-%d', '%Y-%m-%d-%H'],
			// @codeCoverageIgnoreStart
			default => throw new LycheeInvalidArgumentException('Unsupported database driver'),
			// @codeCoverageIgnoreEnd
		};
		$format = $formats[(int) array_search($length, self::DATE_LENGTHS, true)];

		return match ($driver) {
			'pgsql' => "to_char({$column}, '{$format}')",
			'sqlite' => "strftime('{$format}', {$column})",
			default => "DATE_FORMAT({$column}, '{$format}')",
		};
	}

	private static function photoDateLength(TimelinePhotoGranularity $granularity): int
	{
		return match ($granularity) {
			TimelinePhotoGranularity::YEAR => 4,
			TimelinePhotoGranularity::MONTH => 7,
			TimelinePhotoGranularity::DAY => 10,
			TimelinePhotoGranularity::HOUR => 13,
			// @codeCoverageIgnoreStart
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid resolved granularity for photos'),
			// @codeCoverageIgnoreEnd
		};
	}

	private static function albumDateLength(TimelineAlbumGranularity $granularity): int
	{
		return match ($granularity) {
			TimelineAlbumGranularity::YEAR => 4,
			TimelineAlbumGranularity::MONTH => 7,
			TimelineAlbumGranularity::DAY => 10,
			// @codeCoverageIgnoreStart
			TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid resolved granularity for albums'),
			// @codeCoverageIgnoreEnd
		};
	}

	private static function isAlphabetical(ConfigManager $config, string $mode_key): bool
	{
		return $config->getValueAsEnum($mode_key, TitleBucketMode::class) === TitleBucketMode::ALPHABETICAL;
	}

	private static function prefixLength(ConfigManager $config, string $length_key): int
	{
		return min(max(1, $config->getValueAsInt($length_key)), self::MAX_PREFIX_LENGTH);
	}

	private static function direction(OrderSortingType $order): string
	{
		return $order === OrderSortingType::DESC ? 'desc' : 'asc';
	}
}
