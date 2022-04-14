<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;

class PHPVersionCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		// As we cannot test this as those are just raising warnings which we cannot check via Travis.
		// I hereby solemnly declare this code as covered !
		// @codeCoverageIgnoreStart

		// 30 Nov 2019	 => 7.2 = DEPRECATED = ERROR
		// 28 Nov 2019	 => 7.4 = RELEASED   => 7.3 = WARNING
		// 26 Nov 2020	 => 8.0 = RELEASED   => 7.4 = WARNING
		// 6 Dec 2020	 => 7.3 = DEPRECATED = ERROR
		// ! 25 Nov 2021	 => 8.1 = Released   => 8.0 = WARNING & 7.4 = ERROR
		$php_error = 8;
		$php_warning = 8;
		$php_latest = 8.1;

		// ! 26 Nov 2022	 => 8.0 = DEPRECATED = ERROR
		// $php_error = 8.1;
		// $php_warning = 8.1;
		// $php_latest = 8.1;

		if (floatval(phpversion()) < $php_latest) {
			$errors[] = 'Info: Latest version of PHP is ' . $php_latest;
		}

		if (floatval(phpversion()) < $php_error) {
			$errors[] = 'Error: Upgrade to PHP ' . $php_warning . ' or higher';
		}

		if (floatval(phpversion()) < $php_warning && $php_error < $php_warning) {
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
