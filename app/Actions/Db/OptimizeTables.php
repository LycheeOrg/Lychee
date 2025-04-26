<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Db;

use App\Enum\DbDriverType;
use Illuminate\Support\Facades\Schema;

class OptimizeTables extends BaseOptimizer
{
	/**
	 * @return array<int, string>
	 */
	public function do(): array
	{
		$ret = ['Optimizing tables.'];
		$driver_name = $this->getDriverType($ret);
		/** @var array{name:string,schema:?string,size:int,comment:?string,collation:?string,engine:?string}[] */
		$tables = Schema::getTables();

		/** @var string|null $sql */
		$sql = match ($driver_name) {
			DbDriverType::MYSQL => 'ANALYZE TABLE ',
			DbDriverType::PGSQL => 'ANALYZE ',
			DbDriverType::SQLITE => 'ANALYZE ',
			default => null,
		};

		if ($sql === null) {
			// @codeCoverageIgnoreStart
			return $ret;
			// @codeCoverageIgnoreEnd
		}

		foreach ($tables as $table) {
			$this->execStatement($sql . $table['name'], $table['name'] . ' analyzed.', $ret);
		}

		return $ret;
	}
}
