<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use Illuminate\Support\Facades\DB;

/**
 * We list the foreign keys.
 * This is useful to debug when Lychee is getting pretty slow.
 */
class ForeignKeyListInfo implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (config('database.list_foreign_keys') === false) {
			return $next($data);
		}

		match (DB::getDriverName()) {
			'sqlite' => $this->sqlite($data),
			'mysql' => $this->mysql($data),
			'pgsql' => $this->pgsql($data),
			default => '',
		};

		return $next($data);
	}

	/**
	 * @param DiagnosticData[] $data
	 *
	 * @return void
	 */
	private function sqlite(array &$data): void
	{
		$fks = DB::select("SELECT m.name , p.* FROM sqlite_master m JOIN pragma_foreign_key_list(m.name) p ON m.name != p.\"table\" WHERE m.type = 'table' ORDER BY m.name;");

		foreach ($fks as $fk) {
			$data[] = DiagnosticData::info(
				sprintf('Foreign key: %-30s → %-20s : %s', $fk->name . '.' . $fk->from, $fk->table . '.' . $fk->to, strval($fk->on_update)),
				self::class
			);
		}
	}

	/**
	 * @param DiagnosticData[] $data
	 *
	 * @return void
	 */
	private function mysql(array &$data): void
	{
		$fks = DB::select('select *
from information_schema.referential_constraints fks
join information_schema.key_column_usage kcu on fks.constraint_schema = kcu.table_schema
and fks.table_name = kcu.table_name
and fks.constraint_name = kcu.constraint_name
group by fks.constraint_schema, fks.table_name, fks.unique_constraint_schema, fks.referenced_table_name, fks.constraint_name
order by fks.constraint_schema, fks.table_name;
');
		foreach ($fks as $fk) {
			$data[] = DiagnosticData::info(
				sprintf('Foreign key: %-30s → %-20s : %s', $fk->TABLE_NAME . '.' . $fk->COLUMN_NAME, $fk->REFERENCED_TABLE_NAME . '.' . $fk->REFERENCED_COLUMN_NAME, strval($fk->UPDATE_RULE)),
				self::class
			);
		}
	}

	/**
	 * @param DiagnosticData[] $data
	 *
	 * @return void
	 */
	private function pgsql(array &$data): void
	{
		$fks = DB::select('SELECT tc.table_schema, tc.constraint_name, tc.table_name, kcu.column_name,
ccu.table_schema AS foreign_table_schema,
ccu.table_name AS foreign_table_name,
ccu.column_name AS foreign_column_name
FROM
information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
  ON tc.constraint_name = kcu.constraint_name
  AND tc.table_schema = kcu.table_schema
JOIN information_schema.constraint_column_usage AS ccu
  ON ccu.constraint_name = tc.constraint_name
  AND ccu.table_schema = tc.table_schema
WHERE tc.constraint_type = \'FOREIGN KEY\';');

		foreach ($fks as $fk) {
			$data[] = DiagnosticData::info(sprintf('Foreign key: %-30s → %-20s', $fk->table_name . '.' . $fk->column_name, $fk->foreign_table_name . '.' . $fk->foreign_column_name), self::class);
		}
	}
}
