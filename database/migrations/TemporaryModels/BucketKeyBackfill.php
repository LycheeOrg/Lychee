<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Support\Facades\DB;
use Safe\DateTimeImmutable;
use function Safe\preg_match;

/**
 * Standalone copy of the Feature 092 key computation
 * ({@see \App\Services\BucketKeys}, {@see \App\Services\PhotoBucketComputer},
 * {@see \App\Services\AlbumBucketComputer}), frozen for the Feature 092
 * backfill migrations. Migrations must not depend on live app code, because
 * app code may change or be removed while old migrations must remain
 * runnable.
 */
class BucketKeyBackfill
{
	public const CHUNK_SIZE = 1000;

	private const MAX_PREFIX_LENGTH = 255;

	public static function takenKey(?string $raw_taken_at, ?string $orig_tz): ?string
	{
		if ($raw_taken_at === null) {
			return null;
		}

		$utc = new \DateTimeZone('UTC');
		$zone = ($orig_tz === null || $orig_tz === '') ? $utc : new \DateTimeZone($orig_tz);

		return (new DateTimeImmutable($raw_taken_at, $utc))->setTimezone($zone)->format('Y-m-d-H');
	}

	public static function titleDateKey(string $title): ?string
	{
		if (preg_match('/^(\d{4})(?|(\d{2})(\d{2})|(?:-(\d{2}))?(?:-(\d{2}))?)/', trim($title), $matches) !== 1) {
			return null;
		}

		$year = $matches[1];
		$month = ($matches[2] ?? '') === '' ? '01' : $matches[2];
		$day = ($matches[3] ?? '') === '' ? '01' : $matches[3];

		return checkdate((int) $month, (int) $day, (int) $year) ? $year . '-' . $month . '-' . $day : null;
	}

	/**
	 * Effective sort column of a photo listing or of an album's children:
	 * the album's own column when both column and order are set, else the
	 * instance default.
	 */
	public static function effectiveColumn(?string $column, ?string $order, string $default): string
	{
		return ($column === null || $order === null) ? $default : $column;
	}

	public static function config(string $key, string $default): string
	{
		$value = DB::table('configs')->where('key', '=', $key)->value('value');

		return is_string($value) && $value !== '' ? $value : $default;
	}

	public static function photoKey(string $column, string $title_mode, object $photo): ?string
	{
		return match ($column) {
			'taken_at' => $photo->taken_key,
			'created_at' => substr((string) $photo->created_at, 0, 10) . '-' . substr((string) $photo->created_at, 11, 2),
			'title' => $title_mode === 'alphabetical'
				? mb_substr((string) $photo->title_base, 0, self::MAX_PREFIX_LENGTH)
				: $photo->title_date_key,
			'is_highlighted' => in_array($photo->is_highlighted, [true, 1, '1', 't'], true) ? '1' : '0',
			'type' => (string) $photo->type,
			'rating_avg' => $photo->rating_avg === null ? null : (string) (int) round((float) $photo->rating_avg),
			default => null,
		};
	}

	public static function albumKey(string $column, string $title_mode, object $album): ?string
	{
		return match ($column) {
			'created_at' => substr((string) $album->created_at, 0, 10),
			'min_taken_at' => $album->min_taken_at === null ? null : substr((string) $album->min_taken_at, 0, 10),
			'max_taken_at' => $album->max_taken_at === null ? null : substr((string) $album->max_taken_at, 0, 10),
			'title' => $title_mode === 'alphabetical'
				? mb_substr((string) $album->title_base, 0, self::MAX_PREFIX_LENGTH)
				: self::titleDateKey((string) $album->title),
			default => null,
		};
	}
}
