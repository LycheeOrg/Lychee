<?php

namespace App\ControllerFunctions\Diagnostics;

class GDSupportCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		if (function_exists('gd_info')) {
			$gdVersion = gd_info();
			if (!$gdVersion['JPEG Support']) {
				$errors[] = 'Error: PHP gd extension without jpeg support';
			}
			if (!$gdVersion['PNG Support']) {
				$errors[] = 'Error: PHP gd extension without png support';
			}
			if (
				!$gdVersion['GIF Read Support']
				|| !$gdVersion['GIF Create Support']
			) {
				$errors[] = 'Error: PHP gd extension without full gif support';
			}
		}
	}
}
