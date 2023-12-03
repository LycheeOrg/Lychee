<?php

namespace App\Actions\Db;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeDb
{
	public function do(): array
	{
		$ret = ['Optimizing Database.'];

		$connection = Schema::connection(null)->getConnection();
		$driverName = $connection->getDriverName();
		$tables = $connection->getDoctrineSchemaManager()->listTableNames();

		$ret[] = match ($driverName) {
			'mysql' => 'MySql/MariaDB detected.',
			'pgsql' => 'PostgreSQL detected.',
			'sqlite' => 'SQLite detected.',
			default => 'Warning:Unknown DBMS; doing nothing.',
		};

		/** @var string|null $sql */
		$sql = match ($driverName) {
			'mysql' => 'OPTIMIZE TABLE ',
			'pgsql' => 'VACUUM(FULL, ANALYZE)',
			'sqlite' => 'VACUUM',
			default => null,
		};

		if ($sql === null) {
			return $ret;
		}

		if ($driverName === 'mysql') {
			foreach ($tables as $table) {
				try {
					DB::statement($sql . $table);
					$ret[] = $table . ' optimized.';
				} catch (\Throwable $th) {
					$ret[] = 'Error: could not optimize ' . $table . '.';
					$ret[] = 'Error: ' . $th->getMessage();
				}
			}
		} else {
			try {
				DB::statement($sql);
				$ret[] = 'DB optimized.';
			} catch (\Throwable $th) {
				$ret[] = 'Error: could not optimize DB.';
				$ret[] = 'Error: ' . $th->getMessage();
			}
		}

		return $ret;
	}
}
