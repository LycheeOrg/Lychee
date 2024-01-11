<?php

namespace App\Enum;

use App\Enum\Traits\DecorateBackedEnum;

/**
 * Enum OauthProvidersType.
 *
 * Available providers
 */
enum OauthProvidersType: string
{
	use DecorateBackedEnum;

	case AMAZON = 'amazon';
	case APPLE = 'apple';
	case FACEBOOK = 'facebook';
	case GITHUB = 'github';
	case GOOGLE = 'google';
	case MASTODON = 'mastodon';
	case MICROSOFT = 'microsoft';
	case NEXTCLOUD = 'nextcloud';
}
