<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use Carbon\CarbonTimeZone;
use Closure;

class TimezoneCheck implements DiagnosticPipe
{
	public function handle(array &$data, Closure $next): array
	{
		$timezone = CarbonTimeZone::create();
		if ($timezone === false) {
			$data[]
				= 'Error: Could not retrieve timezone; you might experience strange results when importing photos without explicit EXIF timezone';

			return $next($data);
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
