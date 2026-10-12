<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Actions\Diagnostics\Pipes\Infos\DockerVersionInfo;
use App\Constants\JobQueue;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;

/**
 * The recompute jobs (album statistics, covers, sizes, buckets) are pushed on
 * the `low` queue: a worker which does not list it never runs them.
 *
 * The LycheeOrg images already start their worker with `default,low`;
 * every other setup (bare metal, linuxserver.io, custom images) is told how.
 */
class RecomputeQueueCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$connection = config('queue.default');
		if ($connection === 'sync' || $this->isLycheeOrgImage()) {
			return $next($data);
		}

		$default_queue = config('queue.connections.' . $connection . '.queue', 'default');
		$data[] = DiagnosticData::info(
			'Queue: album statistics, covers, sizes and buckets are recomputed on the "' . JobQueue::LOW . '" queue.',
			self::class,
			['Start the queue worker with: php artisan queue:work --queue=' . $default_queue . ',' . JobQueue::LOW]
		);

		return $next($data);
	}

	protected function isLycheeOrgImage(): bool
	{
		$docker = new DockerVersionInfo();

		return $docker->isLycheeOrg() || $docker->isLycheeFrankenPHP();
	}
}
