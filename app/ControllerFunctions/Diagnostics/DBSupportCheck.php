<?php

namespace App\ControllerFunctions\Diagnostics;

class DBSupportCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
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
			if (config('database.default') == $db_possibility[0]) {
				$found = true;
				if (!extension_loaded($db_possibility[1])) {
					$errors[] = 'Error: ' . $db_possibility[0] . ' db driver selected and PHP ' . $db_possibility[1] . ' extension not activated';
				}
			}
		}
		if (!$found) {
			$errors[] = 'Error: could not find the database solution for ' . config('database.default');
		}
	}
}
