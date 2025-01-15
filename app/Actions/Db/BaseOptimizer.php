<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Db;

use App\Enum\DbDriverType;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class BaseOptimizer
{
	private Connection $connection;

	/**
	 * Initialization of the connection.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->connection = Schema::connection(null)->getConnection();
	}

	/**
	 * Get the kind of driver used.
	 *
	 * @param array<int,string> $ret reference array for return messages
	 *
	 * @return DbDriverType|null
	 */
	protected function getDriverType(array &$ret): DbDriverType|null
	{
		$driverName = DbDriverType::tryFrom($this->connection->getDriverName());

		$ret[] = match ($driverName) {
			DbDriverType::MYSQL => 'MySql/MariaDB detected.',
			DbDriverType::PGSQL => 'PostgreSQL detected.',
			DbDriverType::SQLITE => 'SQLite detected.',
			default => 'Warning:Unknown DBMS.',
		};

		return $driverName;
	}

	/**
	 * Do the stuff.
	 *
	 * @return array<int,string>
	 */
	abstract public function do(): array;

	/**
	 * Execute SQL statement.
	 *
	 * @param string            $sql     statment to be executed
	 * @param string            $success success message
	 * @param array<int,string> $ret     reference array for return messages
	 *
	 * @return void
	 */
	protected function execStatement(string $sql, string $success, array &$ret): void
	{
		try {
			DB::statement($sql);
			$ret[] = $success;
			// @codeCoverageIgnoreStart
		} catch (\Throwable $th) {
			$ret[] = 'Error: ' . $th->getMessage();
		}
		// @codeCoverageIgnoreEnd
	}
}
