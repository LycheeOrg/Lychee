<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Db;

use App\Enum\DbDriverType;
use Illuminate\Support\Facades\Schema;

class OptimizeDb extends BaseOptimizer
{
	/**
	 * @return array<int,string>
	 */
	public function do(): array
	{
		$ret = ['Optimizing Database.'];
		$driverName = $this->getDriverType($ret);
		/** @var array{name:string,schema:?string,size:int,comment:?string,collation:?string,engine:?string}[] */
		$tables = Schema::getTables();

		/** @var string|null $sql */
		$sql = match ($driverName) {
			DbDriverType::MYSQL => 'OPTIMIZE TABLE ',
			DbDriverType::PGSQL => 'VACUUM(FULL, ANALYZE)',
			DbDriverType::SQLITE => 'VACUUM',
			default => null,
		};

		if ($driverName === DbDriverType::MYSQL) {
			foreach ($tables as $table) {
				$this->execStatement($sql . $table['name'], $table['name'] . ' optimized.', $ret);
			}
		} elseif ($driverName !== null) {
			$this->execStatement($sql, 'DB optimized.', $ret);
		}

		return $ret;
	}
}
