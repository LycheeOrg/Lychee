<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use Illuminate\Support\Facades\DB;

class ForeignKeyListInfo implements DiagnosticPipe
{
	public function handle(array &$data, \Closure $next): array
	{
		if (config('database.list_foreign_keys') === false) {
			return $next($data);
		}

		match (DB::getDriverName()) {
			'sqlite' => $this->sqlite($data),
			'mysql' => $this->mysql($data),
			'pgsql' => '',
			default => ''
		};

		return $next($data);
	}

	private function sqlite(array &$data): void
	{
		$fks = DB::select("SELECT m.name , p.* FROM sqlite_master m JOIN pragma_foreign_key_list(m.name) p ON m.name != p.\"table\" WHERE m.type = 'table' ORDER BY m.name;");

		foreach ($fks as $fk) {
			$data[] = sprintf('Foreign key: %-30s → %-20s : %s', $fk->name . '.' . $fk->from, $fk->table . '.' . $fk->to, $fk->on_update);
		}
	}

	private function mysql(array &$data): void
	{
		$fks = DB::select('select
	   *
from information_schema.referential_constraints fks
join information_schema.key_column_usage kcu on fks.constraint_schema = kcu.table_schema
and fks.table_name = kcu.table_name
and fks.constraint_name = kcu.constraint_name
group by fks.constraint_schema, fks.table_name, fks.unique_constraint_schema, fks.referenced_table_name, fks.constraint_name
order by fks.constraint_schema, fks.table_name;
');
		foreach ($fks as $fk) {
			$data[] = sprintf('Foreign key: %-30s → %-20s : %s',
				$fk->TABLE_NAME . '.' . $fk->COLUMN_NAME,
				$fk->REFERENCED_TABLE_NAME . '.' . $fk->REFERENCED_COLUMN_NAME,
				$fk->UPDATE_RULE);
		}
	}
}

