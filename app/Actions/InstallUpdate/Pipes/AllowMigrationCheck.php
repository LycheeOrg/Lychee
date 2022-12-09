<?php

namespace App\Actions\InstallUpdate\Pipes;

use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\Config;

class AllowMigrationCheck extends AbstractUpdaterPipe
{
	public const ERROR_MSG =
		/* @lang text */
		'Update not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.';

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		if (Config::get('app.env') !== 'production') {
			return $next($output);
		}

		// @codeCoverageIgnoreStart
		// we cannot code cov this part. APP_ENV is dev in testing mode.
		if (Configs::getValueAsBool('force_migration_in_production')) {
			Logs::warning(__METHOD__, __LINE__, 'Force update is production.');

			return $next($output);
		}

		$output[] = self::ERROR_MSG;
		Logs::warning(__METHOD__, __LINE__, self::ERROR_MSG);

		return $output;
		// @codeCoverageIgnoreEnd
	}
}