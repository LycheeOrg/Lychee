<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Origin of a moderation history event.
 *
 * UPLOAD and NSFW_SCAN are made by the system; ADMIN by an administrator.
 */
enum ModerationSource: string
{
	case UPLOAD = 'upload';
	case NSFW_SCAN = 'nsfw_scan';
	case ADMIN = 'admin';
}
