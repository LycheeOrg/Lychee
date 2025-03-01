<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;

/**
 * We want to make sure that our users are using OPcache for faster php code execution.
 */
class OpCacheCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$opcache_conf = opcache_get_configuration();

		if (
			!is_array($opcache_conf) ||
			!isset($opcache_conf['directives']['opcache.enable']) ||
			$opcache_conf['directives']['opcache.enable'] === '0'
		) {
			$data[] = DiagnosticData::warn('OPcache is not enabled.', self::class, ['Enabling it will improve performance.']);
		}

		return $next($data);
	}
}
