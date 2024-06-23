<?php

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
		$timezone = CarbonTimeZone::create(config('app.timezone'));
		if ($timezone === null) {
			// @codeCoverageIgnoreStart
			$data[]
				= 'Error: Could not retrieve timezone; you might experience strange results when importing photos without explicit EXIF timezone';

			return $next($data);
			// @codeCoverageIgnoreEnd
		}
		// @phpstan-ignore-next-line : create returns null or object.
		$timezoneName = $timezone->getName();
		$tzArray = explode('/', $timezoneName);

		if (count($tzArray) !== 2 || $tzArray[0] === 'Etc') {
			$data[]
				= 'Warning: Default timezone not properly set; you might experience strange results when importing photos without explicit EXIF timezone';
		}

		return $next($data);
	}
}
