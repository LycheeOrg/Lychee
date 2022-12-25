<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;

class PHPVersionCheck implements DiagnosticPipe
{
	public function handle(array &$data, \Closure $next): array
	{
		$this->checkPhpVersion($data);
		$this->check32Bits($data);
		$this->checkExtensions($data);

		return $next($data);
	}

	private function checkPhpVersion(array &$data): void
	{
		// As we cannot test this as those are just raising warnings which we cannot check via Travis.
		// I hereby solemnly declare this code as covered !
		// @codeCoverageIgnoreStart

		// 30 Nov 2019	 => 7.2 = DEPRECATED = ERROR
		// 28 Nov 2019	 => 7.4 = RELEASED   => 7.3 = WARNING
		// 26 Nov 2020	 => 8.0 = RELEASED   => 7.4 = WARNING
		// 6 Dec 2020	 => 7.3 = DEPRECATED = ERROR
		// 25 Nov 2021	 => 8.1 = Released   => 8.0 = WARNING & 7.4 = ERROR
		// ! 08 Dec 2022	 => 8.0 = DEPRECATED = ERROR
		$php_error = 8.0;
		$php_warning = 8.1;
		$php_latest = 8.2;

		if (floatval(phpversion()) <= $php_error) {
			$data[] = 'Error: Upgrade to PHP ' . $php_warning . ' or higher';
		} elseif (floatval(phpversion()) < $php_warning) {
			$data[] = 'Warning: Upgrade to PHP ' . $php_latest . ' or higher';
		} elseif (floatval(phpversion()) < $php_latest) {
			$data[] = 'Info: Latest version of PHP is ' . $php_latest;
		}
	}

	private function check32Bits(array &$data): void
	{
		// 32 or 64 bits ?
		if (PHP_INT_MAX === 2147483647) {
			$data[] = 'Warning: Using 32 bit PHP, recommended upgrade to 64 bit';
		}
	}

	private function checkExtensions(array &$data): void
	{
		// Extensions
		$extensions = [
			'bcmath', // Required by Laravel
			'ctype', // Required by Laravel
			'dom', // Required by dependencies
			'exif',
			'fileinfo', // Required by Laravel
			'filter', // Required by dependencies
			'gd',
			'json', // Required by Laravel
			'libxml', // Required by dependencies
			'mbstring', // Required by Laravel
			'openssl', // Required by Laravel
			'pcre', // Required by dependencies
			'PDO', // Required by Laravel
			'Phar', // Required by dependencies
			'SimpleXML', // Required by dependencies
			'tokenizer', // Required by Laravel
			'xml', // Required by Laravel
			'xmlwriter', // Required by dependencies
		];

		foreach ($extensions as $extension) {
			if (!extension_loaded($extension)) {
				$data[] = 'Error: PHP ' . $extension . ' extension not activated';
			}
		}
	}
}
