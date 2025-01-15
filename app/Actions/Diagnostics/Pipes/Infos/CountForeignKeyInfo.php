<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticStringPipe;
use Illuminate\Support\Facades\DB;

/**
 * Instead of listing all Foreign key as in the Errors, we just check their number.
 */
class CountForeignKeyInfo implements DiagnosticStringPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		match (DB::getDriverName()) {
			'sqlite' => $this->sqlite($data),
			'mysql' => $this->mysql($data),
			'pgsql' => $this->pgsql($data),
			default => '',
		};

		return $next($data);
	}

	/**
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
	private function sqlite(array &$data): void
	{
		$fks = DB::select("SELECT m.name , p.* FROM sqlite_master m JOIN pragma_foreign_key_list(m.name) p ON m.name != p.\"table\" WHERE m.type = 'table' ORDER BY m.name;");
		$data[] = Diagnostics::line('Number of foreign key:', sprintf('%d found.', count($fks)));
	}

	/**
	 * @param array<int,string> $data
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

		$data[] = Diagnostics::line('Number of foreign key:', sprintf('%d found.', count($fks)));
	}

	/**
	 * @param array<int,string> $data
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

		$data[] = Diagnostics::line('Number of foreign key:', sprintf('%d found.', count($fks)));
	}
}

