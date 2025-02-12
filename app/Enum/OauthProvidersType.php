<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
	case AUTHELIA = 'authelia';
	case AUTHENTIK = 'authentik';
	case FACEBOOK = 'facebook';
	case GITHUB = 'github';
	case GOOGLE = 'google';
	case MASTODON = 'mastodon';
	case MICROSOFT = 'microsoft';
	case NEXTCLOUD = 'nextcloud';
	case KEYCLOAK = 'keycloak';
}
