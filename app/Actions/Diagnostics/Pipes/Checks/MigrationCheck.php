<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\InstalledVersion;

class MigrationCheck implements DiagnosticPipe
{
	/**
	 * @param array<int,string> $data list of error messages
	 * @param \Closure(array<int,string> $data): array<int,string> $next
	 *
	 * @return array<int,string>
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!self::isUpToDate()) {
			$data[] = 'Error: Database is behind file version. Please apply the migrations.';
		}

		if ($this->isInFuture()) {
			$data[] = 'Warning: Database is in advance of file version. Please check your installation.';
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
