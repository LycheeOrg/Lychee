<?php

namespace App\Actions\Db;

use App\Enum\DbDriverType;

class OptimizeTables extends BaseOptimizer
{
	public function do(): array
	{
		$ret = ['Optimizing tables.'];
		$driverName = $this->getDriverType($ret);
		$tables = $this->getTables();

		/** @var string|null $sql */
		$sql = match ($driverName) {
			DbDriverType::MYSQL => 'ANALYZE TABLE ',
			DbDriverType::PGSQL => 'ANALYZE ',
			DbDriverType::SQLITE => 'ANALYZE ',
			default => null,
		};

		if ($sql === null) {
			return $ret;
		}

		foreach ($tables as $table) {
			$this->execStatement($sql . $table, $table . ' analyzed.', $ret);
		}

		return $ret;
	}
}
