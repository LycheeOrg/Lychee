<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

/**
 * FlushQueue - Maintenance controller for managing the job queue.
 *
 * This controller provides functionality to:
 * - Check the number of pending jobs in the queue
 * - Clear all pending jobs from the queue
 *
 * Behavior Based on Queue Configuration:
 * - If queue is 'sync': Returns 0 (no persistent queue)
 * - If queue is 'database': Counts/clears jobs from the jobs table
 * - Other queue drivers (redis, sqs, etc.): May not be fully supported
 *
 * CAUTION: Clearing the queue will delete all pending jobs permanently.
 * This cannot be undone. Only use when you need to reset the queue state.
 */
class FlushQueue extends Controller
{
	/**
	 * Clear all pending jobs from the queue.
	 *
	 * CAUTION: This permanently deletes all pending jobs.
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return void
	 */
	public function do(MaintenanceRequest $request): void
	{
		$queue_connection = Config::get('queue.default', 'sync');

		// Sync queue has no persistent storage, nothing to clear
		if ($queue_connection === 'sync') {
			return;
		}

		// For database queue, use Laravel's clear method if available
		if ($queue_connection === 'database') {
			// Truncate the jobs table to clear all pending jobs
			DB::table('jobs')->truncate();
		} else {
			// For other queue drivers (redis, sqs, etc.), attempt to use the clear method
			// This requires the queue to implement ClearableQueue interface
			$queue = Queue::connection($queue_connection);

			if (method_exists($queue, 'clear')) {
				$queue->clear(Config::get("queue.connections.{$queue_connection}.queue", 'default'));
			}
		}
	}

	/**
	 * Count the number of pending jobs in the queue.
	 *
	 * Returns the count of jobs currently waiting to be processed.
	 *
	 * @param MaintenanceRequest $request Authenticated maintenance request (admin only)
	 *
	 * @return int Total number of pending jobs in the queue
	 */
	public function check(MaintenanceRequest $request): int
	{
		$queue_connection = Config::get('queue.default', 'sync');

		// Sync queue has no persistent storage, always 0
		if ($queue_connection === 'sync') {
			return 0;
		}

		// For database queue, count jobs in the jobs table
		if ($queue_connection === 'database') {
			return DB::table('jobs')->count();
		}

		// For other queue drivers, attempt to get queue size
		$queue = Queue::connection($queue_connection);

		if (method_exists($queue, 'size')) {
			return $queue->size(Config::get("queue.connections.{$queue_connection}.queue", 'default'));
		}

		// If we can't determine the size, return 0
		return 0;
	}
}
