<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Constants;

class TestConstants
{
	public const PATH_IMPORT_DIR = 'uploads/import/';

	public const MIME_TYPE_APP_PDF = 'application/pdf';
	public const MIME_TYPE_IMG_GIF = 'image/gif';
	public const MIME_TYPE_IMG_JPEG = 'image/jpeg';
	public const MIME_TYPE_IMG_PNG = 'image/png';
	public const MIME_TYPE_IMG_TIFF = 'image/tiff';
	public const MIME_TYPE_IMG_WEBP = 'image/webp';
	public const MIME_TYPE_IMG_XCF = 'image/x-xcf';
	public const MIME_TYPE_IMG_HEIC = 'image/heic';
	public const MIME_TYPE_IMG_HEIF = 'image/heif';
	public const MIME_TYPE_VID_MP4 = 'video/mp4';
	public const MIME_TYPE_VID_QUICKTIME = 'video/quicktime';
	public const MIME_TYPE_APP_ZIP = 'application/zip';

	public const SAMPLE_DOWNLOAD_JPG = 'https://lycheeorg.dev/test_data/mongolia.jpeg';
	public const SAMPLE_DOWNLOAD_JPG_WITHOUT_EXTENSION = 'https://lycheeorg.dev/test_data/mongolia';
	// public const SAMPLE_DOWNLOAD_TIFF = 'https://lycheeorg.dev/test_data/tiff.tif';
	// public const SAMPLE_DOWNLOAD_TIFF_WITHOUT_EXTENSION = 'https://lycheeorg.dev/test_data/tiff';

	public const SAMPLE_TEST_ZIP = 'tests/Samples/test_photos.zip';
	public const SAMPLE_FILE_AARHUS = 'tests/Samples/aarhus.jpg';
	public const SAMPLE_FILE_ETTLINGEN = 'tests/Samples/ettlinger-alb.jpg';
	public const SAMPLE_FILE_GAMING_VIDEO = 'tests/Samples/gaming.mp4';
	public const SAMPLE_FILE_GIF = 'tests/Samples/gif.gif';
	public const SAMPLE_FILE_GMP_BROKEN_IMAGE = 'tests/Samples/google_motion_photo_broken.jpg';
	public const SAMPLE_FILE_GMP_IMAGE = 'tests/Samples/google_motion_photo.jpg';
	public const SAMPLE_FILE_HOCHUFERWEG = 'tests/Samples/hochuferweg.jpg';
	public const SAMPLE_FILE_MONGOLIA_IMAGE = 'tests/Samples/mongolia.jpeg';
	public const SAMPLE_FILE_NIGHT_IMAGE = 'tests/Samples/night.jpg';
	public const SAMPLE_FILE_ORIENTATION_180 = 'tests/Samples/orientation-180.jpg';
	public const SAMPLE_FILE_ORIENTATION_270 = 'tests/Samples/orientation-270.jpg';
	public const SAMPLE_FILE_ORIENTATION_90 = 'tests/Samples/orientation-90.jpg';
	public const SAMPLE_FILE_ORIENTATION_HFLIP = 'tests/Samples/orientation-hflip.jpg';
	public const SAMPLE_FILE_ORIENTATION_VFLIP = 'tests/Samples/orientation-vflip.jpg';
	public const SAMPLE_FILE_PDF = 'tests/Samples/pdf.pdf';
	public const SAMPLE_FILE_PNG = 'tests/Samples/png.png';
	public const SAMPLE_FILE_SUNSET_IMAGE = 'tests/Samples/fin de journée.jpg';
	public const SAMPLE_FILE_TIFF = 'tests/Samples/tiff.tif';
	public const SAMPLE_FILE_TRAIN_IMAGE = 'tests/Samples/train.jpg';
	public const SAMPLE_FILE_TRAIN_VIDEO = 'tests/Samples/train.mov';
	public const SAMPLE_FILE_UNDEFINED_EXIF_TAG = 'tests/Samples/undefined-exif-tag.jpg';
	public const SAMPLE_FILE_WEBP = 'tests/Samples/webp.webp';
	public const SAMPLE_FILE_WITHOUT_EXIF = 'tests/Samples/without_exif.jpg';
	public const SAMPLE_FILE_XCF = 'tests/Samples/xcf.xcf';
	public const SAMPLE_FILE_HEIC = 'tests/Samples/classic-car.heic';
	public const SAMPLE_FILE_HEIF = 'tests/Samples/sewing-threads.heic';

	public const SAMPLE_FILES_2_MIME = [
		self::SAMPLE_FILE_AARHUS => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ETTLINGEN => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_GAMING_VIDEO => self::MIME_TYPE_VID_MP4,
		self::SAMPLE_FILE_GIF => self::MIME_TYPE_IMG_GIF,
		self::SAMPLE_FILE_GMP_BROKEN_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_GMP_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_HOCHUFERWEG => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_MONGOLIA_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_NIGHT_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_180 => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_270 => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_90 => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_HFLIP => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_ORIENTATION_VFLIP => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_PDF => self::MIME_TYPE_APP_PDF,
		self::SAMPLE_FILE_PNG => self::MIME_TYPE_IMG_PNG,
		self::SAMPLE_FILE_SUNSET_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_TIFF => self::MIME_TYPE_IMG_TIFF,
		self::SAMPLE_FILE_TRAIN_IMAGE => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_TRAIN_VIDEO => self::MIME_TYPE_VID_QUICKTIME,
		self::SAMPLE_FILE_UNDEFINED_EXIF_TAG => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_WEBP => self::MIME_TYPE_IMG_WEBP,
		self::SAMPLE_FILE_WITHOUT_EXIF => self::MIME_TYPE_IMG_JPEG,
		self::SAMPLE_FILE_XCF => self::MIME_TYPE_IMG_XCF,
		self::SAMPLE_TEST_ZIP => self::MIME_TYPE_APP_ZIP,
		self::SAMPLE_FILE_HEIC => self::MIME_TYPE_IMG_HEIC,
		self::SAMPLE_FILE_HEIF => self::MIME_TYPE_IMG_HEIF,
	];

	public const CONFIG_ALBUMS_SORTING_COL = 'sorting_albums_col';
	public const CONFIG_ALBUMS_SORTING_ORDER = 'sorting_albums_order';
	public const CONFIG_DEFAULT_ALBUM_PROTECTION = 'default_album_protection';
	public const CONFIG_DOWNLOADABLE = 'grants_download';
	public const CONFIG_HAS_EXIF_TOOL = 'has_exiftool';
	public const CONFIG_HAS_FFMPEG = 'has_ffmpeg';
	public const CONFIG_HAS_IMAGICK = 'imagick';
	public const CONFIG_MAP_DISPLAY = 'map_display';
	public const CONFIG_MAP_DISPLAY_PUBLIC = 'map_display_public';
	public const CONFIG_MAP_INCLUDE_SUBALBUMS = 'map_include_subalbums';
	public const CONFIG_PHOTOS_SORTING_COL = 'sorting_photos_col';
	public const CONFIG_PHOTOS_SORTING_ORDER = 'sorting_photos_order';
	public const CONFIG_RAW_FORMATS = 'raw_formats';
	public const CONFIG_USE_LAST_MODIFIED_DATE_WHEN_NO_EXIF = 'use_last_modified_date_when_no_exif_date';

	public const PHOTO_NIGHT_TITLE = 'night';
	public const PHOTO_MONGOLIA_TITLE = 'mongolia';
	public const PHOTO_SUNSET_TITLE = 'fin de journée';
	public const PHOTO_TRAIN_TITLE = 'train';

	public const ALBUM_TITLE_1 = 'Test Album 1';
	public const ALBUM_TITLE_2 = 'Test Album 2';
	public const ALBUM_TITLE_3 = 'Test Album 3';
	public const ALBUM_TITLE_4 = 'Test Album 4';
	public const ALBUM_TITLE_5 = 'Test Album 5';
	public const ALBUM_TITLE_6 = 'Test Album 6';

	public const ALBUM_PWD_1 = 'Album Password 1';
	public const ALBUM_PWD_2 = 'Album Password 2';
	public const ALBUM_PWD_3 = 'Album Password 3';

	public const USER_NAME_1 = 'Test User 1';
	public const USER_NAME_2 = 'Test User 2';
	public const USER_NAME_3 = 'Test User 3';

	public const USER_PWD_1 = 'User Password 1';
	public const USER_PWD_2 = 'User Password 2';
	public const USER_PWD_3 = 'User Password 3';

	/** @var array[] EXPECTED_PHOTO_JSON defines the expected JSON result for each sample image such that we can avoid repeating this again and again during the tests */
	public const EXPECTED_PHOTO_JSON = [
		self::SAMPLE_FILE_NIGHT_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_NIGHT_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 1, 'width' => 6720, 'height' => 4480],
				'medium2x' => ['type' => 2, 'width' => 3240, 'height' => 2160],
				'medium' => ['type' => 3, 'width' => 1620, 'height' => 1080],
				'small2x' => ['type' => 4, 'width' => 1080,	'height' => 720],
				'small' => ['type' => 5, 'width' => 540, 'height' => 360],
				'thumb2x' => ['type' => 6, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 7, 'width' => 200, 'height' => 200],
			],
		],
		self::SAMPLE_FILE_MONGOLIA_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_MONGOLIA_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 1, 'width' => 1280, 'height' => 850],
				'medium2x' => null,
				'medium' => null,
				'small2x' => ['type' => 4, 'width' => 1084,	'height' => 720],
				'small' => ['type' => 5, 'width' => 542, 'height' => 360],
				'thumb2x' => ['type' => 6, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 7, 'width' => 200, 'height' => 200],
			],
		],
		self::SAMPLE_FILE_SUNSET_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_SUNSET_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 1, 'width' => 914, 'height' => 1625],
				'medium2x' => null,
				'medium' => ['type' => 3, 'width' => 607, 'height' => 1080],
				'small2x' => ['type' => 4, 'width' => 405,	'height' => 720],
				'small' => ['type' => 5, 'width' => 202, 'height' => 360],
				'thumb2x' => ['type' => 6, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 7, 'width' => 200, 'height' => 200],
			],
		],
		self::SAMPLE_FILE_TRAIN_IMAGE => [
			'id' => null,
			'album_id' => null,
			'title' => self::PHOTO_TRAIN_TITLE,
			'type' => 'image/jpeg',
			'size_variants' => [
				'original' => ['type' => 1, 'width' => 4032, 'height' => 3024],
				'medium2x' => ['type' => 2, 'width' => 2880, 'height' => 2160],
				'medium' => ['type' => 3, 'width' => 1440, 'height' => 1080],
				'small2x' => ['type' => 4, 'width' => 960,	'height' => 720],
				'small' => ['type' => 5, 'width' => 480, 'height' => 360],
				'thumb2x' => ['type' => 6, 'width' => 400, 'height' => 400],
				'thumb' => ['type' => 7, 'width' => 200, 'height' => 200],
			],
		],
	];

	public const EXPECTED_UNAUTHENTICATED_MSG = 'User is not authenticated';
	public const EXPECTED_FORBIDDEN_MSG = 'Insufficient privileges';
	public const EXPECTED_PASSWORD_REQUIRED_MSG = 'Password required';
}