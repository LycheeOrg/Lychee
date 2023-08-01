<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;

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
					$data[] = 'Error: ' . $db_possibility[0] . ' db driver selected and PHP ' . $db_possibility[1] . ' extension not activated';
					// @codeCoverageIgnoreEnd
				}
			}
		}
		if (!$found) {
			// @codeCoverageIgnoreStart
			$data[] = 'Error: could not find the database solution for ' . config('database.default');
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
