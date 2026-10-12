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
 * The LycheeOrg images start their worker with `QUEUE_NAMES` (default
 * `default,low`): warn when it was set without `low`. Every other setup
 * (bare metal, linuxserver.io, custom images) is told the worker command.
 */
class RecomputeQueueCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$connection = config('queue.default');
		if ($connection === 'sync') {
			return $next($data);
		}

		$diagnostic = $this->isLycheeOrgImage()
			? $this->checkWorkerQueues()
			: $this->workerCommandInfo($connection);
		if ($diagnostic !== null) {
			$data[] = $diagnostic;
		}

		return $next($data);
	}

	protected function isLycheeOrgImage(): bool
	{
		return (new DockerVersionInfo())->isLycheeOrgImage();
	}

	/**
	 * Only sees `QUEUE_NAMES` when it is set in the environment shared with
	 * the web container, not on the worker service alone.
	 */
	private function checkWorkerQueues(): ?DiagnosticData
	{
		$queue_names = trim((string) config('queue.worker_queues'));
		if ($queue_names === '' || in_array(JobQueue::LOW, array_map('trim', explode(',', $queue_names)), true)) {
			return null;
		}

		return DiagnosticData::warn(
			'Queue: QUEUE_NAMES does not include "' . JobQueue::LOW . '": album statistics, covers, sizes and buckets are never recomputed.',
			self::class,
			['Set QUEUE_NAMES=' . $queue_names . ',' . JobQueue::LOW . ', or remove it to use the default (default,' . JobQueue::LOW . ').']
		);
	}

	private function workerCommandInfo(string $connection): DiagnosticData
	{
		$default_queue = config('queue.connections.' . $connection . '.queue', 'default');

		return DiagnosticData::info(
			'Queue: album statistics, covers, sizes and buckets are recomputed on the "' . JobQueue::LOW . '" queue.',
			self::class,
			['Start the queue worker with: php artisan queue:work --queue=' . $default_queue . ',' . JobQueue::LOW]
		);
	}
}
