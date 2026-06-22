<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

enum NsfwPreset: string
{
	case DEFAULT = 'default';
	case STRICT = 'strict';
	case MODERATION = 'moderation';
	case NUDE_FEMALE = 'nude_female';
	case PERMISSIVE = 'permissive';
	case SOCIAL_MEDIA = 'social_media';
}
