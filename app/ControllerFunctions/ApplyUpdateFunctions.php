<?php

namespace App\ControllerFunctions;

use App\Configs;
use App\Logs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class ApplyUpdateFunctions
{
	/**
	 * Arrayify a string and append it to $output.
	 *
	 * @param $string
	 * @param array $output
	 *
	 * @return array
	 */
	private function str_to_array($string, array &$output)
	{
		$a = explode("\n", $string);
		foreach ($a as $aa) {
			if ($aa != '') {
				$output[] = $aa;
			}
		}

		return $output;
	}

	/**
	 * call git over exec.
	 *
	 * @param array $output
	 */
	private function git_pull(array &$output)
	{
		$command = 'git pull ' . Config::get('urls.git.pull') . ' master 2>&1';
		exec($command, $output);
	}

	/**
	 * call for migrate via the Artisan Facade.
	 *
	 * @param array $output
	 */
	private function internal(array &$output)
	{
		// if we are in a production environment we actually require a double check.
		if (env('APP_ENV', 'production') == 'production') {
			// @codeCoverageIgnoreStart
			// we cannot code cov this part. APP_ENV is dev in testing mode.
			if (Configs::get_value('force_migration_in_production') == '1') {
				Logs::warning(__METHOD__, __LINE__, 'Force migration is production.');
				Artisan::call('migrate', ['--force' => true]);
				$this->str_to_array(Artisan::output(), $output);
			} else {
				$output[] = 'Migration not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.';
				Logs::warning(__METHOD__, __LINE__, 'Migration not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.');
			}
			// @codeCoverageIgnoreEnd
		} else {
			Artisan::call('migrate');
			$this->str_to_array(Artisan::output(), $output);
		}
	}

	/**
	 * Apply the migration:
	 * 1. git pull
	 * 2. artisan migrate.
	 *
	 * Put DB_MIGRATE_ART=true in .env to use the internal version instead of the shelled version.
	 * this is a test version on live. We probably will remove this distinction after more testing on live.
	 *
	 * @return array
	 */
	public function apply()
	{
		$output = [];
		$this->git_pull($output);
		$this->internal($output);

		return $output;
	}
}
