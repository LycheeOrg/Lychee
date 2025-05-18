<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;

/**
 * This event is fired when a direct link to an album is used.
 */
final class AlbumShared extends BaseAlbumMetricsEvent
{
	public function metricAction(): MetricsAction
	{
		return MetricsAction::SHARED;
	}
}
