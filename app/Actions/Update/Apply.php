<?php

namespace App\Actions\Update;

use App\Contracts\Versions\GitHubVersionControl;
use App\Contracts\Versions\LycheeVersionInterface;
use App\Exceptions\Internal\FrameworkException;
use App\Facades\Helpers;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use function Safe\chdir;
use function Safe\exec;
use function Safe\preg_replace;
use function Safe\putenv;

class Apply
{
	public const ERROR_MSG =
		/* @lang text */
		'Update not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.';

	/**
	 * @param string[] $output
	 *
	 * @return bool
	 */
	private function check_prod_env_allow_migration(array &$output): bool
	{
		if (Config::get('app.env') === 'production') {
			// @codeCoverageIgnoreStart
			// we cannot code cov this part. APP_ENV is dev in testing mode.
			if (Configs::getValueAsBool('force_migration_in_production')) {
				Logs::warning(__METHOD__, __LINE__, 'Force update is production.');

				return true;
			}

			$output[] = self::ERROR_MSG;
			Logs::warning(__METHOD__, __LINE__, self::ERROR_MSG);

			return false;
			// @codeCoverageIgnoreEnd
		}

		return true;
	}

	/**
	 * call composer over exec.
	 *
	 * @param string[] $output the per-line console output
	 *
	 * @return void
	 *
	 * @throws FrameworkException
	 */
	private function call_composer(array &$output): void
	{
		if (Helpers::isExecAvailable()) {
			try {
				if (Configs::getValueAsBool('apply_composer_update')) {
					// @codeCoverageIgnoreStart
					Logs::warning(__METHOD__, __LINE__, 'Composer is called on update.');

					// Composer\Factory::getHomeDir() method
					// needs COMPOSER_HOME environment variable set
					putenv('COMPOSER_HOME=' . base_path('/composer-cache'));
					chdir(base_path());
					exec('composer install --no-dev --no-progress 2>&1', $output);
					chdir(base_path('public'));
				// @codeCoverageIgnoreEnd
				} else {
					$output[] = 'Composer update are always dangerous when automated.';
					$output[] = 'So we did not execute it.';
					$output[] = 'If you want to have composer update applied, please set the setting to 1 at your own risk.';
				}
			} catch (BindingResolutionException $e) {
				throw new FrameworkException('Laravel\'s container component', $e);
			}
		}
	}

	/**
	 * call git over exec.
	 *
	 * @param string[] $output the per-line console output
	 *
	 * @return void
	 */
	private function git_pull(array &$output): void
	{
		if (Helpers::isExecAvailable()) {
			$command = 'git pull --rebase ' . Config::get('urls.git.pull') . ' master 2>&1';
			exec($command, $output);
		}
	}

	/**
	 * Runs the migration via the Artisan Facade.
	 *
	 * **TODO:** Consolidate with {@link \App\Actions\Install\ApplyMigration::migrate()}.
	 *
	 * **ATTENTION:** This method serves the same purpose as
	 * `ApplyMigration::migrate()`.
	 * The whole code around installation/upgrade/migration should
	 * thoroughly be revised an refactored.
	 *
	 * @param string[] $output list of messages
	 *
	 * @return void
	 */
	public function migrate(array &$output): void
	{
		Artisan::call('migrate', ['--force' => true]);

		$a = explode("\n", Artisan::output());
		foreach ($a as $aa) {
			if ($aa !== '') {
				$output[] = $aa;
			}
		}
	}

	/**
	 * Clean coloring from the command line.
	 *
	 * @param string[] $output the per-line console output
	 *
	 * @return void
	 */
	public function filter(array &$output): void
	{
		$output = preg_replace('/\033[[][0-9]*;*[0-9]*;*[0-9]*m/', '', $output);
	}

	/**
	 * Applies the migration:
	 * 1. git pull
	 * 2. artisan migrate.
	 *
	 * @return string[] the per-line console output
	 *
	 * @throws FrameworkException
	 */
	public function run(): array
	{
		$lycheeVersion = resolve(LycheeVersionInterface::class);
		$githubFunctions = resolve(GitHubVersionControl::class);
		$githubFunctions->hydrate(false);

		$output = [];
		if (
			$githubFunctions->isMasterBranch() &&
			$this->check_prod_env_allow_migration($output)
		) {
			if (!$lycheeVersion->isRelease()) {
				$this->git_pull($output);
			}
			$this->migrate($output);
			if (!$lycheeVersion->isRelease()) {
				$this->call_composer($output);
			}
		}
		$this->filter($output);

		return $output;
	}
}
