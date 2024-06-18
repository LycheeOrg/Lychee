<?php

declare(strict_types=1);

namespace App\Actions\Db;

use App\Enum\DbDriverType;

class OptimizeDb extends BaseOptimizer
{
	/**
	 * @return array<int, string>
	 */
	public function do(): array
	{
		$ret = ['Optimizing Database.'];
		$driverName = $this->getDriverType($ret);
		$tables = $this->getTables();

		/** @var string|null $sql */
		$sql = match ($driverName) {
			DbDriverType::MYSQL => 'OPTIMIZE TABLE ',
			DbDriverType::PGSQL => 'VACUUM(FULL, ANALYZE)',
			DbDriverType::SQLITE => 'VACUUM',
			default => null,
		};

		if ($driverName === DbDriverType::MYSQL) {
			foreach ($tables as $table) {
				$this->execStatement($sql . $table, $table . ' optimized.', $ret);
			}
		} elseif ($driverName !== null) {
			$this->execStatement($sql, 'DB optimized.', $ret);
		}

		return $ret;
	}
}
