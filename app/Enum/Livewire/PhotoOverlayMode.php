<?php

namespace App\Enum\Livewire;

enum PhotoOverlayMode: string
{
	case NONE = 'none';
	case DESC = 'desc';
	case EXIF = 'exif';
	case DATE = 'date';

	/**
	 * Convert the enum into it's translated format.
	 * Note that it is missing owner.
	 *
	 * @return array<string,string>
	 */
	public static function localized(): array
	{
		return [
			self::EXIF => __('lychee.OVERLAY_EXIF'),
			self::DESC => __('lychee.OVERLAY_DESCRIPTION'),
			self::DATE => __('lychee.OVERLAY_DATE'),
			self::NONE => __('lychee.OVERLAY_NONE'),
		];
	}
}
