<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Enum\ColumnSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Internal\TimelineGranularityException;
use App\Http\Resources\Models\PhotoResource;
use App\Http\Resources\Models\ThumbAlbumResource;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TimelineData extends Data
{
	public function __construct(
		public string $time_date,
		public string $format,
	) {
	}

	public static function fromPhoto(PhotoResource $photo, TimelinePhotoGranularity $granularity): self
	{
		$timeline_date_format_year = request()->configs()->getValueAsString('timeline_photo_date_format_year');
		$timeline_date_format_month = request()->configs()->getValueAsString('timeline_photo_date_format_month');
		$timeline_date_format_day = request()->configs()->getValueAsString('timeline_photo_date_format_day');
		$timeline_date_format_hour = request()->configs()->getValueAsString('timeline_photo_date_format_hour');

		$format = match ($granularity) {
			TimelinePhotoGranularity::YEAR => $photo->timeline_date_carbon()->format($timeline_date_format_year),
			TimelinePhotoGranularity::MONTH => $photo->timeline_date_carbon()->format($timeline_date_format_month),
			TimelinePhotoGranularity::DAY => $photo->timeline_date_carbon()->format($timeline_date_format_day),
			TimelinePhotoGranularity::HOUR => $photo->timeline_date_carbon()->format($timeline_date_format_hour),
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new TimelineGranularityException(),
		};

		$time_date = match ($granularity) {
			TimelinePhotoGranularity::YEAR => $photo->timeline_date_carbon()->format('Y'),
			TimelinePhotoGranularity::MONTH => $photo->timeline_date_carbon()->format('Y-m'),
			TimelinePhotoGranularity::DAY => $photo->timeline_date_carbon()->format('Y-m-d'),
			TimelinePhotoGranularity::HOUR => $photo->timeline_date_carbon()->format('Y-m-d H'),
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED => throw new LycheeLogicException('DEFAULT is not a valid granularity for photos'),
		};

		return new TimelineData(time_date: $time_date, format: $format);
	}

	/**
	 * Attempts to parse a date from a title string.
	 *
	 * @param string $title The title string to parse
	 *
	 * @return ?Carbon The parsed Carbon date object or null if parsing fails
	 */
	private static function parseDateFromTitle(string $title): ?Carbon
	{
		// A title is expected to be in one of the following formats:
		// "YYYY something"
		// "YYYY-MM something"
		// "YYYY-MM-DD something"
		// We match the first part that looks like a date.
		// Then use Carbon to create a date object from the matched components.
		$pattern = '/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?/';
		try {
			if (preg_match($pattern, $title, $matches) === 1) {
				$year = intval($matches[1]);
				$month = intval($matches[2] ?? 1);
				$day = intval($matches[3] ?? 1);

				return Carbon::createFromDate($year, $month, $day);
			}

			return null;
		} catch (PcreException $e) {
			// fail silently.
			return null;
		}
	}

	private static function fromAlbum(ThumbAlbumResource $album, ColumnSortingType $column_sorting, TimelineAlbumGranularity $granularity): ?self
	{
		$timeline_date_format_year = request()->configs()->getValueAsString('timeline_album_date_format_year');
		$timeline_date_format_month = request()->configs()->getValueAsString('timeline_album_date_format_month');
		$timeline_date_format_day = request()->configs()->getValueAsString('timeline_album_date_format_day');
		$date = match ($column_sorting) {
			ColumnSortingType::CREATED_AT => $album->created_at_carbon(),
			ColumnSortingType::MAX_TAKEN_AT => $album->max_taken_at_carbon(),
			ColumnSortingType::MIN_TAKEN_AT => $album->min_taken_at_carbon(),
			// Parse the title as date (e.g. "2020 something" or "2020-03 something" or "2020-03-25 something")
			ColumnSortingType::TITLE => self::parseDateFromTitle($album->title),
			default => null,
		};

		if ($date === null) {
			return null;
		}

		$format = match ($granularity) {
			TimelineAlbumGranularity::YEAR => $date->format($timeline_date_format_year),
			TimelineAlbumGranularity::MONTH => $date->format($timeline_date_format_month),
			TimelineAlbumGranularity::DAY => $date->format($timeline_date_format_day),
			TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED => throw new TimelineGranularityException(),
		};

		$time_date = match ($granularity) {
			TimelineAlbumGranularity::YEAR => $date->format('Y'),
			TimelineAlbumGranularity::MONTH => $date->format('Y-m'),
			TimelineAlbumGranularity::DAY => $date->format('Y-m-d'),
			TimelineAlbumGranularity::DEFAULT, TimelineAlbumGranularity::DISABLED => throw new LycheeLogicException('DEFAULT/DISABLED is not a valid granularity for albums'),
		};

		return new TimelineData(time_date: $time_date, format: $format);
	}

	/**
	 * @param Collection<int,ThumbAlbumResource> $albums
	 * @param ColumnSortingType                  $column_sorting
	 * @param TimelineAlbumGranularity           $granularity
	 *
	 * @return Collection<int,ThumbAlbumResource>
	 */
	public static function setTimeLineDataForAlbums(Collection $albums, ColumnSortingType $column_sorting, TimelineAlbumGranularity $granularity): Collection
	{
		return $albums->map(function (ThumbAlbumResource $album) use ($column_sorting, $granularity) {
			$album->timeline = TimelineData::fromAlbum($album, $column_sorting, $granularity);

			return $album;
		});
	}

	/**
	 * @param Collection<int,PhotoResource> $photos
	 * @param TimelinePhotoGranularity      $granularity
	 *
	 * @return Collection<int,PhotoResource>
	 */
	public static function setTimeLineDataForPhotos(Collection $photos, TimelinePhotoGranularity $granularity): Collection
	{
		return $photos->map(function (PhotoResource $photo) use ($granularity) {
			$photo->timeline = TimelineData::fromPhoto($photo, $granularity);

			return $photo;
		});
	}

	public static function fromDate(string $date): TimelineData
	{
		$granularity = request()->configs()->getValueAsEnum('timeline_photos_granularity', TimelinePhotoGranularity::class);
		$timeline_date_format_year = request()->configs()->getValueAsString('timeline_quick_access_date_format_year');
		$timeline_date_format_month = request()->configs()->getValueAsString('timeline_quick_access_date_format_month');
		$timeline_date_format_day = request()->configs()->getValueAsString('timeline_quick_access_date_format_day');
		$timeline_date_format_hour = request()->configs()->getValueAsString('timeline_quick_access_date_format_hour');

		$carbon = $granularity === TimelinePhotoGranularity::YEAR ? Carbon::createFromDate(intval($date)) : Carbon::parse($date);

		$format = match ($granularity) {
			TimelinePhotoGranularity::YEAR => $carbon->format($timeline_date_format_year),
			TimelinePhotoGranularity::MONTH => $carbon->format($timeline_date_format_month),
			TimelinePhotoGranularity::DAY => $carbon->format($timeline_date_format_day),
			TimelinePhotoGranularity::HOUR => $carbon->format($timeline_date_format_hour),
			TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED, null => throw new TimelineGranularityException(),
		};

		$time_date = $carbon->format($granularity->format());

		return new TimelineData(time_date: $time_date, format: $format);
	}
}