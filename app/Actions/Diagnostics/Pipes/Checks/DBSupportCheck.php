<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;

/**
 * Check that the database is supported.
 * In theory this should be the case by default.
 */
class DBSupportCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$db_possibilities = [
			'mysql' => ['mysql', 'mysqli', 'pdo_mysql'],
			'pgsql' => ['pdo_pgsql', 'pgsql'],
			'sqlite' => ['sqlite3'],
		];

		if (!array_key_exists(config('database.default', 'sqlite'), $db_possibilities)) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error('database type ' . config('database.default', 'sqlite') . ' is not supported by Lychee', self::class);

			return $next($data);
		}
		// @codeCoverageIgnoreEnd

		$found = false;
		foreach ($db_possibilities[config('database.default', 'sqlite')] as $db_possibility) {
			$found = $found || extension_loaded($db_possibility);
		}
		if (!$found) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(config('database.default', 'sqlite') . ' db driver selected and PHP ' . implode(' or ', $db_possibilities[config('database.default', 'sqlite')]) . ' extensions not activated', self::class);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
