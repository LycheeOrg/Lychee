<?php

declare(strict_types=1);

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
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
		$timezone = CarbonTimeZone::create();
		if ($timezone === false) {
			// @codeCoverageIgnoreStart
			$data[]
				= 'Error: Could not retrieve timezone; you might experience strange results when importing photos without explicit EXIF timezone';

			return $next($data);
			// @codeCoverageIgnoreEnd
		}
		$timezoneName = $timezone->getName();
		$tzArray = explode('/', $timezoneName);

		if (count($tzArray) !== 2 || $tzArray[0] === 'Etc') {
			$data[]
				= 'Warning: Default timezone not properly set; you might experience strange results when importing photos without explicit EXIF timezone';
		}

		return $next($data);
	}
}
