<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Enum ThumbAlbumSubtitleType.
 *
 * The kind of info displayed under the title in the thumb.
 */
enum ThumbAlbumSubtitleType: string
{
	case DESCRIPTION = 'description';
	case TAKEDATE = 'takedate';
	case CREATION = 'creation';
	case OLDSTYLE = 'oldstyle';
}