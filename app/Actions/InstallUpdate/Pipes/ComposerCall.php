<?php

namespace App\Actions\InstallUpdate\Pipes;

use App\Facades\Helpers;
use App\Metadata\Versions\LycheeVersion;
use App\Models\Configs;
use App\Models\Logs;
use function Safe\chdir;
use function Safe\exec;
use function Safe\putenv;

class ComposerCall extends AbstractUpdateInstallerPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		$lycheeVersion = resolve(LycheeVersion::class);
		if ($lycheeVersion->isRelease()) {
			return $next($output);
		}

		// update with respect to installed version
		$noDev = $lycheeVersion->isDev() ? '' : '--no-dev ';

		if (Helpers::isExecAvailable()) {
			if (Configs::getValueAsBool('apply_composer_update')) {
				// @codeCoverageIgnoreStart
				Logs::warning(__METHOD__, __LINE__, 'Composer is called on update.');

				// Composer\Factory::getHomeDir() method
				// needs COMPOSER_HOME environment variable set
				putenv('COMPOSER_HOME=' . base_path('/composer-cache'));
				chdir(base_path());
				exec(sprintf('composer install %s--no-progress 2>&1', $noDev), $output);
				chdir(base_path('public'));
			// @codeCoverageIgnoreEnd
			} else {
				$output[] = 'Composer update are always dangerous when automated.';
				$output[] = 'So we did not execute it.';
				$output[] = 'If you want to have composer update applied, please set the setting to 1 at your own risk.';
			}
		}

		return $next($output);
	}
}