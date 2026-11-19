<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * When expired live metrics are deleted on a read of the feed.
 */
enum LiveMetricsCleanup: string
{
	case DEFERRED = 'deferred';
	case SYNC = 'sync';
	case DISABLED = 'disabled';
}
