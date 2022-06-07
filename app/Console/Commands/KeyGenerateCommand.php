<?php

namespace App\Console\Commands;

class KeyGenerateCommand extends \Illuminate\Foundation\Console\KeyGenerateCommand
{
	/**
	 * Set the application key in the environment file.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function setKeyInEnvironmentFile($key): bool
	{
		$currentKey = $this->laravel['config']['app.key'];

		if (strlen($currentKey) !== 0 || (!$this->confirmToProceed())) {
			return false;
		}

		$this->writeNewEnvironmentFileWith($key);

		return true;
	}
}
