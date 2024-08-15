<?php

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
}
