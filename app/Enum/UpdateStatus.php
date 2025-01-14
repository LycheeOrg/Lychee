<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * current UpdateStatus of Lychee.
 * 0 = release or not master branch
 * 1 = up to date
 * 2 = files are bhind the latest online version
 * 3 = database are behind the file version.
 */
enum UpdateStatus: int
{
	case NOT_MASTER = 0;
	case UP_TO_DATE = 1;
	case NOT_UP_TO_DATE = 2;
	case REQUIRE_MIGRATION = 3;
}