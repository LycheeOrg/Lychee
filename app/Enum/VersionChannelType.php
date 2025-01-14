<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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