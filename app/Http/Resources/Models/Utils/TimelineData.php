<?php

namespace App\Http\Resources\Models\Utils;

use App\Enum\ColumnSortingType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Resources\Models\ThumbAlbumResource;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TimelineData extends Data
{
	public function __construct(
		public string $timeDate,
		public string $format,
	) {
	}

	public static function fromPhoto(Photo $photo, TimelinePhotoGranularity $granularity): self
	{
		$timeline_date_format_year = Configs::getValueAsString('timeline_photo_date_format_year');
		$timeline_date_format_month = Configs::getValueAsString('timeline_photo_date_format_month');
		$timeline_date_format_day = Configs::getValueAsString('timeline_photo_date_format_day');
		$timeline_photo_date_format_hour = Configs::getValueAsString('timeline_photo_date_format_hour');

		$format = match ($granularity) {
			TimelinePhotoGranularity::YEAR => ($photo->taken_at ?? $photo->created_at)->format($timeline_date_format_year),
			TimelinePhotoGranularity::MONTH => ($photo->taken_at ?? $photo->created_at)->format($timeline_date_format_month),
			TimelinePhotoGranularity::DAY => ($photo->taken_at ?? $photo->created_at)->format($timeline_date_format_day),
			TimelinePhotoGranularity::HOUR => ($photo->taken_at ?? $photo->created_at)->format($timeline_photo_date_format_hour),
		};

		$timeDate = match ($granularity) {
			TimelinePhotoGranularity::YEAR => ($photo->taken_at ?? $photo->created_at)->format('Y'),
			TimelinePhotoGranularity::MONTH => ($photo->taken_at ?? $photo->created_at)->format('Y-m'),
			TimelinePhotoGranularity::DAY => ($photo->taken_at ?? $photo->created_at)->format('Y-m-d'),
			TimelinePhotoGranularity::HOUR => ($photo->taken_at ?? $photo->created_at)->format('Y-m-d H'),
		};

		return new TimelineData(timeDate: $timeDate, format: $format);
	}

	private static function fromAlbum(ThumbAlbumResource $album, ColumnSortingType $columnSorting, TimelineAlbumGranularity $granularity): ?self
	{
		$timeline_date_format_year = Configs::getValueAsString('timeline_album_date_format_year');
		$timeline_date_format_month = Configs::getValueAsString('timeline_album_date_format_month');
		$timeline_date_format_day = Configs::getValueAsString('timeline_album_date_format_day');
		$date = match ($columnSorting) {
			ColumnSortingType::CREATED_AT => $album->created_at_carbon(),
			ColumnSortingType::MAX_TAKEN_AT => $album->max_taken_at_carbon(),
			ColumnSortingType::MIN_TAKEN_AT => $album->min_taken_at_carbon(),
			default => null,
		};

		if ($date === null) {
			return null;
		}

		$format = match ($granularity) {
			TimelineAlbumGranularity::YEAR => $date->format($timeline_date_format_year),
			TimelineAlbumGranularity::MONTH => $date->format($timeline_date_format_month),
			TimelineAlbumGranularity::DAY => $date->format($timeline_date_format_day),
		};

		$timeDate = match ($granularity) {
			TimelineAlbumGranularity::YEAR => $date->format('Y'),
			TimelineAlbumGranularity::MONTH => $date->format('Y-m'),
			TimelineAlbumGranularity::DAY => $date->format('Y-m-d'),
		};

		return new TimelineData(timeDate: $timeDate, format: $format);
	}

	/**
	 * @param Collection<int,ThumbAlbumResource> $albums
	 * @param ColumnSortingType                  $columnSorting
	 *
	 * @return Collection<int,ThumbAlbumResource>
	 */
	public static function setTimeLineDataForAlbums(Collection $albums, ColumnSortingType $columnSorting, TimelineAlbumGranularity $granularity): Collection
	{
		return $albums->map(function (ThumbAlbumResource $album) use ($columnSorting, $granularity) {
			$album->timeline = TimelineData::fromAlbum($album, $columnSorting, $granularity);

			return $album;
		});
	}
}