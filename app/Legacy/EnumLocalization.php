<?php

namespace App\Legacy;

use App\Enum\AlbumDecorationOrientation;
use App\Enum\AlbumDecorationType;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\ImageOverlayType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use Illuminate\Contracts\Container\BindingResolutionException;

class EnumLocalization
{
	/**
	 * @param string $class
	 *
	 * @return array<string,string>
	 *
	 * @throws BindingResolutionException
	 */
	public static function of(string $class): array
	{
		return match ($class) {
			AlbumDecorationOrientation::class => [
				AlbumDecorationOrientation::ROW->value => __('lychee.ALBUM_DECORATION_ORIENTATION_ROW'),
				AlbumDecorationOrientation::ROW_REVERSE->value => __('lychee.ALBUM_DECORATION_ORIENTATION_ROW_REVERSE'),
				AlbumDecorationOrientation::COLUMN->value => __('lychee.ALBUM_DECORATION_ORIENTATION_COLUMN'),
				AlbumDecorationOrientation::COLUMN_REVERSE->value => __('lychee.ALBUM_DECORATION_ORIENTATION_COLUMN_REVERSE'),
			],
			AlbumDecorationType::class => [
				AlbumDecorationType::NONE->value => __('lychee.ALBUM_DECORATION_NONE'),
				AlbumDecorationType::LAYERS->value => __('lychee.ALBUM_DECORATION_ORIGINAL'),
				AlbumDecorationType::ALBUM->value => __('lychee.ALBUM_DECORATION_ALBUM'),
				AlbumDecorationType::PHOTO->value => __('lychee.ALBUM_DECORATION_PHOTO'),
				AlbumDecorationType::ALL->value => __('lychee.ALBUM_DECORATION_ALL'),
			],
			AspectRatioType::class => [
				AspectRatioType::aspect5by4->value => __('aspect_ratio.5by4'),
				AspectRatioType::aspect4by5->value => __('aspect_ratio.4by5'),
				AspectRatioType::aspect2by3->value => __('aspect_ratio.2by3'),
				AspectRatioType::aspect3by2->value => __('aspect_ratio.3by2'),
				AspectRatioType::aspect1by1->value => __('aspect_ratio.1by1'),
				AspectRatioType::aspect1byx9->value => __('aspect_ratio.1byx9'),
			],
			ColumnSortingAlbumType::class => [
				ColumnSortingAlbumType::CREATED_AT->value => __('lychee.SORT_ALBUM_SELECT_1'),
				ColumnSortingAlbumType::TITLE->value => __('lychee.SORT_ALBUM_SELECT_2'),
				ColumnSortingAlbumType::DESCRIPTION->value => __('lychee.SORT_ALBUM_SELECT_3'),
				ColumnSortingAlbumType::MIN_TAKEN_AT->value => __('lychee.SORT_ALBUM_SELECT_6'),
				ColumnSortingAlbumType::MAX_TAKEN_AT->value => __('lychee.SORT_ALBUM_SELECT_5'),
			],
			ColumnSortingPhotoType::class => [
				ColumnSortingPhotoType::CREATED_AT->value => __('lychee.SORT_PHOTO_SELECT_1'),
				ColumnSortingPhotoType::TAKEN_AT->value => __('lychee.SORT_PHOTO_SELECT_2'),
				ColumnSortingPhotoType::TITLE->value => __('lychee.SORT_PHOTO_SELECT_3'),
				ColumnSortingPhotoType::DESCRIPTION->value => __('lychee.SORT_PHOTO_SELECT_4'),
				ColumnSortingPhotoType::IS_STARRED->value => __('lychee.SORT_PHOTO_SELECT_6'),
				ColumnSortingPhotoType::TYPE->value => __('lychee.SORT_PHOTO_SELECT_7'),
			],
			ImageOverlayType::class => [
				ImageOverlayType::EXIF->value => __('lychee.OVERLAY_EXIF'),
				ImageOverlayType::DESC->value => __('lychee.OVERLAY_DESCRIPTION'),
				ImageOverlayType::DATE->value => __('lychee.OVERLAY_DATE'),
				ImageOverlayType::NONE->value => __('lychee.OVERLAY_NONE'),
			],
			OrderSortingType::class => [
				OrderSortingType::ASC->value => __('lychee.SORT_ASCENDING'),
				OrderSortingType::DESC->value => __('lychee.SORT_DESCENDING'),
			],
			PhotoLayoutType::class => [
				PhotoLayoutType::SQUARE->value => __('lychee.LAYOUT_SQUARES'),
				PhotoLayoutType::JUSTIFIED->value => __('lychee.LAYOUT_JUSTIFIED'),
				PhotoLayoutType::MASONRY->value => __('lychee.LAYOUT_MASONRY'),
				PhotoLayoutType::GRID->value => __('lychee.LAYOUT_GRID'),
			],
			default => [],
		};
	}
}