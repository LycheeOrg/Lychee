<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\PhotoVisited;
use App\Models\Configs;

/**
 * Just logging of Cache events.
 */
class MetricsListener
{
	/**
	 * Handle the event.
	 */
	public function handle(PhotoVisited $event): void
	{
		if (Configs::getValueAsBool('cache_event_logging') === false) {
			return;
		}



	}
}
