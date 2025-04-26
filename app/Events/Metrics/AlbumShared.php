<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;

/**
 * This event is fired when a direct link to an album is used.
 */
final class AlbumShared extends BaseMetricsEvent
{
	public function key(): string
	{
		return 'album_id';
	}

	public function metricAction(): MetricsAction
	{
		return MetricsAction::SHARED;
	}
}
