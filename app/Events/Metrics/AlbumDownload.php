<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Events\Metrics;

use App\Enum\MetricsAction;

/**
 * This event is fired when an album is downloaded.
 */
final class AlbumDownload extends BaseMetricsEvent
{
	public function key(): string
	{
		return 'album_id';
	}

	public function metricAction(): MetricsAction
	{
		return MetricsAction::DOWNLOAD;
	}
}
