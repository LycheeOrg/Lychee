<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use Closure;

class DBSupportCheck implements DiagnosticPipe
{
	public function handle(array &$data, Closure $next): array
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
					$data[] = 'Error: ' . $db_possibility[0] . ' db driver selected and PHP ' . $db_possibility[1] . ' extension not activated';
				}
			}
		}
		if (!$found) {
			$data[] = 'Error: could not find the database solution for ' . config('database.default');
		}

		return $next($data);
	}
}
