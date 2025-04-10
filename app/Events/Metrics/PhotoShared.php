<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;

/**
 * This event is fired when a direct link to a photo is used.
 */
class PhotoShared extends BaseMetricsEvent
{
	public function key(): string
	{
		return 'photo_id';
	}

	public function metricAction(): MetricsAction
	{
		return MetricsAction::SHARED;
	}
}
