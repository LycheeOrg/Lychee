<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum ImageOverlayType.
 *
 * All the allowed overlay info on Photos
 */
enum ImageOverlayType: string
{
	case NONE = 'none';
	case DESC = 'desc';
	case DATE = 'date';
	case EXIF = 'exif';

	/**
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		return [
			self::EXIF->value => __('lychee.OVERLAY_EXIF'),
			self::DESC->value => __('lychee.OVERLAY_DESCRIPTION'),
			self::DATE->value => __('lychee.OVERLAY_DATE'),
			self::NONE->value => __('lychee.OVERLAY_NONE'),
		];
	}
}
