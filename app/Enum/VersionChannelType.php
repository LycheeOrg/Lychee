<?php

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