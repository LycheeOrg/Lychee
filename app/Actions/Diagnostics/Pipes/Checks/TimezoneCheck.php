<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use Carbon\CarbonTimeZone;

/**
 * quick check that the Timezone has been set.
 */
class TimezoneCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$timezone = CarbonTimeZone::create(config('app.timezone'));
		if ($timezone === null) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error('Could not retrieve timezone; you might experience strange results when importing photos without explicit EXIF timezone', self::class);

			return $next($data);
			// @codeCoverageIgnoreEnd
		}
		$timezone_name = $timezone->getName();
		$tz_array = explode('/', $timezone_name);

		if (count($tz_array) !== 2 || $tz_array[0] === 'Etc') {
			$data[] = DiagnosticData::warn('Default timezone not properly set; you might experience strange results when importing photos without explicit EXIF timezone', self::class);
		}

		return $next($data);
	}
}
