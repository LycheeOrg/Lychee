<?php

namespace App\ControllerFunctions\Update;

use App\Metadata\LycheeVersion;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class Apply
{
	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @param LycheeVersion $lycheeVersion
	 */
	public function __construct(
		LycheeVersion $lycheeVersion
	) {
		$this->lycheeVersion = $lycheeVersion;
	}

	/**
	 * If we are in a production environment we actually require a double check..
	 *
	 * @param array $output
	 */
	private function check_prod_env_allow_migration(array &$output)
	{
		if (Config::get('app.env') == 'production') {
			// @codeCoverageIgnoreStart
			// we cannot code cov this part. APP_ENV is dev in testing mode.
			if (Configs::get_value('force_migration_in_production') == '1') {
				Logs::warning(__METHOD__, __LINE__, 'Force update is production.');

				return true;
			}

			$output[] = 'Update not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.';
			Logs::warning(__METHOD__, __LINE__, 'Update not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.');

			return false;
			// @codeCoverageIgnoreEnd
		}

		return true;
	}

	/**
	 * call composer over exec.
	 *
	 * @param array $output
	 */
	private function call_composer(array &$output)
	{
		if (Configs::get_value('apply_composer_update', '0') == '1') {
			// @codeCoverageIgnoreStart
			Logs::warning(__METHOD__, __LINE__, 'Composer is called on update.');

			// Composer\Factory::getHomeDir() method
			// needs COMPOSER_HOME environment variable set
			putenv('COMPOSER_HOME=' . base_path('/composer-cache'));
			chdir(base_path());
			exec('composer install --no-dev --no-progress --no-suggest 2>&1', $output);
			chdir(base_path('public'));
		// @codeCoverageIgnoreEnd
		} else {
			$output[] = 'Composer update are always dangerous when automated.';
			$output[] = 'So we did not execute it.';
			$output[] = 'If you want to have composer update applied, please set the setting to 1 at your own risk.';
		}
	}

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
		$command = 'git pull --rebase ' . Config::get('urls.git.pull') . ' master 2>&1';
		exec($command, $output);
	}

	/**
	 * call for migrate via the Artisan Facade.
	 *
	 * @param array $output
	 */
	public function artisan(array &$output)
	{
		Artisan::call('migrate', ['--force' => true]);
		$this->str_to_array(Artisan::output(), $output);
	}

	/**
	 * Clean coloring from the command line.
	 */
	public function filter(array &$output)
	{
		$output = preg_replace('/\033[[][0-9]*;*[0-9]*;*[0-9]*m/', '', $output);
	}

	/**
	 * Apply the migration:
	 * 1. git pull
	 * 2. artisan migrate.
	 *
	 * @return array
	 */
	public function run()
	{
		$output = [];
		if ($this->check_prod_env_allow_migration($output)) {
			$this->lycheeVersion->isRelease or $this->git_pull($output);
			$this->artisan($output);
			$this->lycheeVersion->isRelease or $this->call_composer($output);
		}
		$this->filter($output);

		return $output;
	}
}
