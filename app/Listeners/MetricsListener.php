<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\Metrics\BaseMetricsEvent;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;

/**
 * Just logging of Cache events.
 */
class MetricsListener
{
	/**
	 * Handle the event.
	 */
	public function handle(BaseMetricsEvent $event): void
	{
		$config_manager = app(ConfigManager::class);

		if ($config_manager->getValueAsBool('metrics_enabled') === true) {
			// Increment the respective metric in the database
			DB::table('statistics')
				->where($event->key(), '=', $event->id)
				->increment($event->metricAction()->column(), 1);
		}

		if ($config_manager->getValueAsBool('live_metrics_enabled') === true) {
			// Add event to the live metrics table
			DB::table('live_metrics')->insert([$event->toArray()]);
		}
	}
}
