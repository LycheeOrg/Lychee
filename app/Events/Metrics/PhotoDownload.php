<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;

/**
 * This event is fired when one or multiple photos are downloaded.
 */
final class PhotoDownload extends BasePhotoMetricsEvent
{
	public function metricAction(): MetricsAction
	{
		return MetricsAction::DOWNLOAD;
	}
}
