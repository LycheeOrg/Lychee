<?php

namespace App\Actions\Db;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeTables
{
	public function do(): array
	{
		$ret = ['Optimizing tables.'];

		$connection = Schema::connection(null)->getConnection();
		$tables = $connection->getDoctrineSchemaManager()->listTableNames();

		$driverName = $connection->getDriverName();

		$ret[] = match ($driverName) {
			'mysql' => 'MySql/MariaDB detected.',
			'pgsql' => 'PostgreSQL detected.',
			'sqlite' => 'SQLite detected.',
			default => 'Warning:Unknown DBMS; doing nothing.',
		};

		$sql = match ($driverName) {
			'mysql' => 'ANALYZE TABLE ',
			'pgsql' => 'ANALYZE ',
			'sqlite' => 'ANALYZE ',
			default => 'NOTHING',
		};

		if ($sql === 'NOTHING') {
			return $ret;
		}

		foreach ($tables as $table) {
			try {
				DB::statement($sql . $table);
				$ret[] = $table . ' analyzed.';
			} catch (\Throwable $th) {
				$ret[] = 'Error: could not analyze ' . $table . '.';
				$ret[] = 'Error: ' . $th->getMessage();
			}
		}

		return $ret;
	}
}
