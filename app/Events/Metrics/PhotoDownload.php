<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;

/**
 * This event is fired when one or multiple photos are downloaded.
 */
final class PhotoDownload extends BaseMetricsEvent
{
	public function key(): string
	{
		return 'photo_id';
	}

	public function metricAction(): MetricsAction
	{
		return MetricsAction::DOWNLOAD;
	}
}
