<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use Carbon\CarbonTimeZone;

class TimezoneCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		$timezoneName = CarbonTimeZone::create()->getName();
		$tzArray = explode('/', $timezoneName);

		if (count($tzArray) !== 2 || $tzArray[0] === 'Etc') {
			$errors[]
				= 'Warning: Default timezone not properly set; you might experience strange results when importing photos without explicit EXIF timezone';
		}
	}
}
