<?php

namespace App\ControllerFunctions\Diagnostics;

class PHPVersionCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		// As we cannot test this as those are just raising warnings which we cannot check via Travis.
		// I hereby solemnly  declare this code as covered !
		// @codeCoverageIgnoreStart

		$php_error = 7.2;
		$php_warning = 7.3;
		$php_latest = 7.4;

		// 30 Nov 2019	 => 7.2 = DEPRECATED = ERROR
		// 28 Nov 2019	 => 7.4 = RELEASED   => 7.3 = WARNING
		// 26 Nov 2020   => 8.0 = RELEASED   => 7.4 = WARNING
		// 6 Dec 2020	 => 7.3 = DEPRECATED = ERROR
		// 28 Nov 2021	 => 7.4 = DEPRECATED = ERROR

		if (floatval(phpversion()) < $php_latest) {
			$errors[] = 'Info: Latest version of PHP is ' . $php_latest;
		}

		if (floatval(phpversion()) < $php_error) {
			$errors[] = 'Error: Upgrade to PHP ' . $php_warning . ' or higher';
		}

		if (floatval(phpversion()) < $php_warning) {
			$errors[] = 'Warning: Upgrade to PHP ' . $php_latest . ' or higher';
		}

		// 32 or 64 bits ?
		if (PHP_INT_MAX == 2147483647) {
			$errors[] = 'Warning: Using 32 bit PHP, recommended upgrade to 64 bit';
		}

		// Extensions
		$extensions = ['session', 'exif', 'mbstring', 'gd', 'PDO', 'json', 'zip'];

		foreach ($extensions as $extension) {
			if (!extension_loaded($extension)) {
				$errors[] = 'Error: PHP ' . $extension . ' extension not activated';
			}
		}
	}
}
