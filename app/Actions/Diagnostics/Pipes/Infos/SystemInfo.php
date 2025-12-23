<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticDTO;
use App\Facades\Helpers;
use App\Http\Resources\GalleryConfigs\UploadConfig;
use Carbon\CarbonTimeZone;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use function Safe\ini_get;

/**
 * What system are we running on?
 */
class SystemInfo implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		// About SQL version
		// @codeCoverageIgnoreStart
		try {
			$sql = match (DB::getDriverName()) {
				'mysql' => ['MySQL', 'select version() as version'],
				'sqlite' => ['SQLite', 'select sqlite_version() as version'],
				'pgsql' => ['PostgreSQL', 'select version() as version'],
				default => [DB::getDriverName(), 'select version() as version'],
			};

			$dbtype = $sql[0];

			$pdo = DB::connection()->getPdo();
			$statement = $pdo->query($sql[1]);
			if ($statement !== false) {
				$dbver = (string) $statement->fetchColumn();
			} else {
				$dbver = 'unknown';
			}
		} catch (QueryException $e) {
			$dbtype = 'Unknown SQL';
			$dbver = 'unknown';
		}

		// @codeCoverageIgnoreEnd

		// Output system information
		$data->data[] = Diagnostics::line('System:', PHP_OS);
		$data->data[] = Diagnostics::line('PHP Version:', phpversion());
		$data->data[] = Diagnostics::line('PHP User agent:', ini_get('user_agent'));
		$time_zone = CarbonTimeZone::create(config('app.timezone')) ?? null;
		$data->data[] = Diagnostics::line('Timezone:', $time_zone?->getName() ?? 'undefined');
		$data->data[] = Diagnostics::line('Max uploaded file size:', ini_get('upload_max_filesize'));
		$data->data[] = Diagnostics::line('Max post size:', ini_get('post_max_size'));
		$data->data[] = Diagnostics::line('Chunk size:', Helpers::getSymbolByQuantity(UploadConfig::getUploadLimit()));
		$data->data[] = Diagnostics::line('Max execution time: ', ini_get('max_execution_time'));
		$data->data[] = Diagnostics::line($dbtype . ' Version:', $dbver);
		$data->data[] = '';

		return $next($data);
	}
}