<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\Laravel;

use Illuminate\Support\Facades\Config;

/**
 * Generate the `APP_KEY` config variable.
 *
 * This class extends the original command by the additional option `--no-override`
 * which - if enabled - let the command silently do nothing, if the variable has already been set.
 * This special mode is needed in the automatic install script which is called by Composer
 * upon package installation.
 */
class KeyGenerateCommand extends \Illuminate\Foundation\Console\KeyGenerateCommand
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'key:generate
	                {--no-override : Do not override an existing key}
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';

	/**
	 * Set the application key in the environment file.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function setKeyInEnvironmentFile($key): bool
	{
		if (!$this->hasOption('no-override') || $this->option('no-override') === false || strlen(Config::get('app.key', '')) === 0) {
			return parent::setKeyInEnvironmentFile($key);
		}

		return false;
	}
}
