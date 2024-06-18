<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Channel types used by Lychee.
 */
enum VersionChannelType: string
{
	case RELEASE = 'release';
	case GIT = 'git';
	case TAG = 'tag';
}