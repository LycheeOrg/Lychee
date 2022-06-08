<?php

namespace App\Console\Commands;

/**
 * Generate the `APP_KEY` config variable.
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
		if (!$this->hasOption('no-override') || !$this->option('no-override')) {
			return parent::setKeyInEnvironmentFile($key);
		}

		return strlen($this->laravel['config']['app.key']) === 0 || parent::setKeyInEnvironmentFile($key);
	}
}
