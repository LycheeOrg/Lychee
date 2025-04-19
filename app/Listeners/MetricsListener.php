<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\Metrics\BaseMetricsEvent;
use App\Models\Configs;
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
		if (Configs::getValueAsBool('metrics_enabled') === true) {
			// Increment the respective metric in the database
			DB::table('statistics')
				->where($event->key(), '=', $event->id)
				->increment($event->metricAction()->column(), 1);
		}

		if (Configs::getValueAsBool('live_metrics_enabled') === true) {
			// Add event to the live metrics table
			DB::table('live_metrics')
				->insert([
					[
						'visitor_id' => $event->visitor_id,
						'action' => $event->metricAction(),
						$event->key() => $event->id,
						'created_at' => now(),
					],
				]);
		}
	}
}
