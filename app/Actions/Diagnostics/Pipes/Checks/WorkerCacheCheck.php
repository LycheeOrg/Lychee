<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Actions\Diagnostics\Pipes\Infos\DockerVersionInfo;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;

/**
 * A LycheeOrg image with an asynchronous queue runs its worker in a separate
 * container: a cache kept inside one container is not seen by the other, so
 * caches evicted by jobs stay stale in the web container (ADR-090-01).
 */
class WorkerCacheCheck implements DiagnosticPipe
{
	/**
	 * Cache drivers whose data stays inside one container.
	 */
	private const PER_CONTAINER_DRIVERS = ['file', 'array', 'apc'];

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$driver = config('cache.stores.' . config('cache.default') . '.driver');
		if (config('queue.default') === 'sync' || !in_array($driver, self::PER_CONTAINER_DRIVERS, true) || !$this->isLycheeOrgImage()) {
			return $next($data);
		}

		$data[] = DiagnosticData::warn(
			'Cache: the "' . $driver . '" cache is not shared between the web and the worker containers.',
			self::class,
			[
				'Jobs run by the worker do not refresh the caches of the web container.',
				'Set CACHE_DRIVER=database or CACHE_DRIVER=redis in .env (both containers).',
			]
		);

		return $next($data);
	}

	protected function isLycheeOrgImage(): bool
	{
		return (new DockerVersionInfo())->isLycheeOrgImage();
	}
}
