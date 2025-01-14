<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Infos;

use App\Actions\Diagnostics\Diagnostics;
use App\Contracts\DiagnosticStringPipe;
use App\Facades\Helpers;
use App\Http\Resources\GalleryConfigs\UploadConfig;
use Carbon\CarbonTimeZone;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use function Safe\ini_get;

/**
 * What system are we running on?
 */
class SystemInfo implements DiagnosticStringPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
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
		$data[] = Diagnostics::line('System:', PHP_OS);
		$data[] = Diagnostics::line('PHP Version:', phpversion());
		$data[] = Diagnostics::line('PHP User agent:', ini_get('user_agent'));
		/** @var CarbonTimeZone|null $timeZone */
		$timeZone = CarbonTimeZone::create(config('app.timezone'));
		$data[] = Diagnostics::line('Timezone:', $timeZone?->getName() ?? 'undefined');
		$data[] = Diagnostics::line('Max uploaded file size:', ini_get('upload_max_filesize'));
		$data[] = Diagnostics::line('Max post size:', ini_get('post_max_size'));
		$data[] = Diagnostics::line('Chunk size:', Helpers::getSymbolByQuantity(UploadConfig::getUploadLimit()));
		$data[] = Diagnostics::line('Max execution time: ', ini_get('max_execution_time'));
		$data[] = Diagnostics::line($dbtype . ' Version:', $dbver);
		$data[] = '';

		return $next($data);
	}
}
