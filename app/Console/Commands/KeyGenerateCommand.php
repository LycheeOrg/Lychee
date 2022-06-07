<?php

namespace App\Console\Commands;

use Illuminate\Encryption\Encrypter;
use Safe\Exceptions\UrlException;

class KeyGenerateCommand extends \Illuminate\Foundation\Console\KeyGenerateCommand
{
	/**
	 * Set the application key in the environment file.
	 *
	 * @param string $key
	 *
	 * @return bool
	 *
	 * @throws UrlException
	 */
	protected function setKeyInEnvironmentFile($key): bool
	{
		$currentKey = $this->laravel['config']['app.key'];
		if (str_starts_with($currentKey, 'base64:')) {
			$currentKey = substr($currentKey, 7);
		}
		$supported = Encrypter::supported(\Safe\base64_decode($currentKey), $this->laravel['config']['app.cipher']);

		if (strlen($currentKey) !== 0 && ($supported || ($this->getDefaultConfirmCallback()() && !$this->confirmToProceed()))) {
			return false;
		}

		$this->writeNewEnvironmentFileWith($key);

		return true;
	}
}
