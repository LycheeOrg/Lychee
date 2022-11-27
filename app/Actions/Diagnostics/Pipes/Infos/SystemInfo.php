<?php

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use Carbon\CarbonTimeZone;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use function Safe\ini_get;

class SystemInfo implements DiagnosticPipe
{
	public function handle(array &$data, \Closure $next): array
	{
		// About SQL version
		// @codeCoverageIgnoreStart
		try {
			switch (DB::getDriverName()) {
				case 'mysql':
					$dbtype = 'MySQL';
					$results = DB::select(DB::raw('select version() as version'));
					$dbver = $results[0]->version;
					break;
				case 'sqlite':
					$dbtype = 'SQLite';
					$results = DB::select(DB::raw('select sqlite_version() as version'));
					$dbver = $results[0]->version;
					break;
				case 'pgsql':
					$dbtype = 'PostgreSQL';
					$results = DB::select(DB::raw('select version() as version'));
					$dbver = $results[0]->version;
					break;
				default:
					$dbtype = DB::getDriverName();
					$results = DB::select(DB::raw('select version() as version'));
					$dbver = $results[0]->version;
					break;
			}
		} catch (QueryException $e) {
			$dbtype = 'Unknown SQL';
			$dbver = 'unknown';
		}

		// @codeCoverageIgnoreEnd

		// Output system information
		$data[] = Diagnostics::line('System:', PHP_OS);
		$data[] = Diagnostics::line('PHP Version:', phpversion());
		$data[] = Diagnostics::line('PHP User agent:', ini_get('user_agent'));
		$timeZone = CarbonTimeZone::create();
		$data[] = Diagnostics::line('Timezone:', ($timeZone !== false ? $timeZone : null)?->getName());
		$data[] = Diagnostics::line('Max uploaded file size:', ini_get('upload_max_filesize'));
		$data[] = Diagnostics::line('Max post size:', ini_get('post_max_size'));
		$data[] = Diagnostics::line('Max execution time: ', ini_get('max_execution_time'));
		$data[] = Diagnostics::line($dbtype . ' Version:', $dbver);
		$data[] = '';

		return $next($data);
	}
}
