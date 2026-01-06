<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

/**
 * Tracks long-running SQL queries that may timeout.
 *
 * This listener works by storing query start information in cache
 * and checking execution time after completion.
 */
class LogQueryTimeout
{
	/**
	 * Handle the event when a query is executed.
	 *
	 * @param QueryExecuted $event
	 *
	 * @return void
	 */
	public function handle(QueryExecuted $event): void
	{
		$max_execution_time = config('octane.max_execution_time', 30) * 1000; // convert to ms
		$critical_threshold = $max_execution_time * 0.9; // 90% of timeout
		$warning_threshold = $max_execution_time * 0.7; // 70% of timeout

		// Log if query is dangerously slow
		if ($event->time >= $critical_threshold) {
			Log::error('🚨 CRITICAL: Query approaching timeout', [
				'execution_time_ms' => $event->time,
				'execution_time_s' => round($event->time / 1000, 2),
				'timeout_limit_s' => $max_execution_time / 1000,
				'percentage' => round(($event->time / $max_execution_time) * 100, 1) . '%',
				'sql' => $event->sql,
				'bindings' => $event->bindings,
				'connection' => $event->connectionName,
				'url' => request()?->fullUrl() ?? 'N/A',
			]);
		} elseif ($event->time >= $warning_threshold) {
			Log::warning('⚠️ WARNING: Slow query detected', [
				'execution_time_ms' => $event->time,
				'execution_time_s' => round($event->time / 1000, 2),
				'timeout_limit_s' => $max_execution_time / 1000,
				'percentage' => round(($event->time / $max_execution_time) * 100, 1) . '%',
				'sql' => $event->sql,
				'bindings' => $event->bindings,
				'connection' => $event->connectionName,
				'url' => request()?->fullUrl() ?? 'N/A',
			]);
		}
	}
}
