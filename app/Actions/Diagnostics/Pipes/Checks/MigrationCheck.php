<?php

declare(strict_types=1);

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\InstalledVersion;

/**
 * Just checking that the Database or the files are in the correct version.
 */
class MigrationCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!self::isUpToDate()) {
			// @codeCoverageIgnoreStart
			$data[] = 'Error: Database is behind file version. Please apply the migrations.';
			// @codeCoverageIgnoreEnd
		}

		if ($this->isInFuture()) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: Database is in advance of file version. Please check your installation.';
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}

	public static function isUpToDate(): bool
	{
		$installedVersion = resolve(InstalledVersion::class);
		$fileVersion = resolve(FileVersion::class);

		$db_ver = $installedVersion->getVersion();
		$file_ver = $fileVersion->getVersion();

		return $db_ver->toInteger() === $file_ver->toInteger();
	}

	private function isInFuture(): bool
	{
		$installedVersion = resolve(InstalledVersion::class);
		$fileVersion = resolve(FileVersion::class);

		$db_ver = $installedVersion->getVersion();
		$file_ver = $fileVersion->getVersion();

		return $db_ver->toInteger() > $file_ver->toInteger();
	}
}
