<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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
			['mysql', 'mysqli'],
			['mysql', 'pdo_mysql'],
			['pgsql', 'pgsql'],
			['pgsql', 'pdo_pgsql'],
			['sqlite', 'sqlite3'],
		];

		$found = false;
		foreach ($db_possibilities as $db_possibility) {
			if (config('database.default') === $db_possibility[0]) {
				$found = true;
				if (!extension_loaded($db_possibility[1])) {
					// @codeCoverageIgnoreStart
					$data[] = DiagnosticData::error($db_possibility[0] . ' db driver selected and PHP ' . $db_possibility[1] . ' extension not activated', self::class);
					// @codeCoverageIgnoreEnd
				}
			}
		}
		if (!$found) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error('could not find the database solution for ' . config('database.default'), self::class);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
