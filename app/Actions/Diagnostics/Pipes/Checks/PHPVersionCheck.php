<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;

class PHPVersionCheck implements DiagnosticPipe
{
	public const PHP_ERROR = 8.0;
	public const PHP_WARNING = 8.1;
	public const PHP_LATEST = 8.2;

	public function handle(array &$data, \Closure $next): array
	{
		$this->checkPhpVersion($data);
		$this->check32Bits($data);
		$this->checkExtensions($data);

		return $next($data);
	}

	private function checkPhpVersion(array &$data): void
	{
		// As we cannot test this as those are just raising warnings which we cannot check via CICD.
		// I hereby solemnly declare this code as covered !
		// @codeCoverageIgnoreStart
		if (floatval(phpversion()) <= self::PHP_ERROR) {
			$data[] = 'Error: Upgrade to PHP ' . self::PHP_WARNING . ' or higher';
		} elseif (floatval(phpversion()) < self::PHP_WARNING) {
			$data[] = 'Warning: Upgrade to PHP ' . self::PHP_LATEST . ' or higher';
		} elseif (floatval(phpversion()) < self::PHP_LATEST) {
			$data[] = 'Info: Latest version of PHP is ' . self::PHP_LATEST;
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
