<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;

/**
 * We want to make sure that our users are using the correct version of PHP.
 */
class PHPVersionCheck implements DiagnosticPipe
{
	// We only support the actively supported version of php.
	// See here: https://www.php.net/supported-versions.php
	public const PHP_ERROR = 8.1;
	public const PHP_WARNING = 8.2;
	public const PHP_LATEST = 8.3;

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$this->checkPhpVersion($data);
		$this->check32Bits($data);
		$this->checkExtensions($data);

		return $next($data);
	}

	/**
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
	private function checkPhpVersion(array &$data): void
	{
		// As we cannot test this as those are just raising warnings which we cannot check via CICD.
		// I hereby solemnly declare this code as covered !
		if (floatval(phpversion()) <= self::PHP_ERROR) {
			// @codeCoverageIgnoreStart
			$data[] = 'Error: Upgrade to PHP ' . self::PHP_WARNING . ' or higher';
		// @codeCoverageIgnoreEnd
		} elseif (floatval(phpversion()) < self::PHP_WARNING) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: Upgrade to PHP ' . self::PHP_LATEST . ' or higher';
		// @codeCoverageIgnoreEnd
		} elseif (floatval(phpversion()) < self::PHP_LATEST) {
			$data[] = 'Info: Latest version of PHP is ' . self::PHP_LATEST;
		}
	}

	/**
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
	private function check32Bits(array &$data): void
	{
		// 32 or 64 bits ?
		if (PHP_INT_MAX === 2147483647) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: Using 32 bit PHP, recommended upgrade to 64 bit';
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
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
				// @codeCoverageIgnoreStart
				$data[] = 'Error: PHP ' . $extension . ' extension not activated';
				// @codeCoverageIgnoreEnd
			}
		}
	}
}
