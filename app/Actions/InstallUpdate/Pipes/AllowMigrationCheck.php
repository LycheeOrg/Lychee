<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate\Pipes;

use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AllowMigrationCheck extends AbstractUpdateInstallerPipe
{
	public const ERROR_MSG =
		/* @lang text */
		' Update not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.';

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		if (Config::get('app.env') !== 'production') {
			return $next($output);
		}

		// @codeCoverageIgnoreStart
		// we cannot code cov this part. APP_ENV is `testing` in testing mode.
		if (Configs::getValueAsBool('force_migration_in_production')) {
			Log::warning(__METHOD__ . ':' . __LINE__ . ' Force update is production.');

			return $next($output);
		}

		$output[] = self::ERROR_MSG;
		Log::warning(__METHOD__ . ':' . __LINE__ . self::ERROR_MSG);

		return $output;
		// @codeCoverageIgnoreEnd
	}
}