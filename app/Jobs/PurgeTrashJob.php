<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Jobs;

use App\Actions\Photo\PurgeTrash;
use App\Actions\Photo\PurgeTrashResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Feature 119 (FR-119-05, DO-119-05): purges the trash after a delete.
 *
 * Dispatched through {@see \App\Actions\Shared\DispatchAfterDelete}: queued
 * on async connections, run after the response on `sync`. Unique until it
 * starts, so a delete while a purge waits adds no job, and a delete while a
 * purge runs queues one, which picks up what the running one missed.
 */
class PurgeTrashJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public function handle(PurgeTrash $purge): void
	{
		$sync = config('queue.default') === 'sync';
		$result = $purge->run(null, self::budget($sync, app()->runningInConsole()));

		if (self::shouldContinue($result, $sync)) {
			self::dispatch();
		}
	}

	/**
	 * Time budget of a run: 10 s on `sync` in an HTTP request (Octane's
	 * limit covers the after-response work), none in console on `sync`
	 * (import), 30 minutes in a queue worker.
	 */
	public static function budget(bool $sync, bool $console): ?float
	{
		if (!$sync) {
			return (float) PurgeTrash::ASYNC_TIME_BUDGET;
		}

		return $console ? null : (float) PurgeTrash::SYNC_TIME_BUDGET;
	}

	/**
	 * With a worker, a run that used its budget with photos left queues the
	 * next one. A run stopped by a failing batch does not, so it cannot loop.
	 */
	public static function shouldContinue(PurgeTrashResult $result, bool $sync): bool
	{
		return !$sync && $result->budget_exhausted && $result->left > 0;
	}
}
