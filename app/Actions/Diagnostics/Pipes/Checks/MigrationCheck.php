<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
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
			$data[] = DiagnosticData::error('Database is behind file version. Please apply the migrations.', self::class);
			// @codeCoverageIgnoreEnd
		}

		if ($this->isInFuture()) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('Database is in advance of file version. Please check your installation.', self::class);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}

	public static function isUpToDate(): bool
	{
		$installed_version = resolve(InstalledVersion::class);
		$file_version = resolve(FileVersion::class);

		$db_ver = $installed_version->getVersion();
		$file_ver = $file_version->getVersion();

		return $db_ver->toInteger() === $file_ver->toInteger();
	}

	private function isInFuture(): bool
	{
		$installed_version = resolve(InstalledVersion::class);
		$file_version = resolve(FileVersion::class);

		$db_ver = $installed_version->getVersion();
		$file_ver = $file_version->getVersion();

		return $db_ver->toInteger() > $file_ver->toInteger();
	}
}
