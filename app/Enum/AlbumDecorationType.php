<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum AlbumDecorationType.
 *
 * All the allowed sorting possibilities on Album
 */
enum AlbumDecorationType: string
{
	case NONE = 'none';
	case LAYERS = 'layers';
	case ALBUM = 'album';
	case PHOTO = 'photo';
	case ALL = 'all';

	/**
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		return [
			self::NONE->value => __('lychee.ALBUM_DECORATION_NONE'),
			self::LAYERS->value => __('lychee.ALBUM_DECORATION_ORIGINAL'),
			self::ALBUM->value => __('lychee.ALBUM_DECORATION_ALBUM'),
			self::PHOTO->value => __('lychee.ALBUM_DECORATION_PHOTO'),
			self::ALL->value => __('lychee.ALBUM_DECORATION_ALL'),
		];
	}
}
