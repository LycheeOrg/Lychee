<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Metadata\Versions\FileVersion;
use App\Metadata\Versions\LycheeVersion;

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
			$data[] = 'Error: Database is behind file versions. Please apply the migration.';
		}

		return $next($data);
	}

	public static function isUpToDate(): bool
	{
		$lycheeVersion = resolve(LycheeVersion::class);
		$fileVersion = resolve(FileVersion::class);

		$db_ver = $lycheeVersion->getVersion();
		$file_ver = $fileVersion->getVersion();

		return $db_ver->toInteger() === $file_ver->toInteger();
	}
}
