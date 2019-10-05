<?php

namespace Installer\Middleware;

class InstallCheck implements Check
{
	/**
	 * @return array|bool
	 */
	public function check()
	{
		$logfile = 'installed.log';
		if (file_exists($logfile))
		{
			return ['lines' => [file_get_contents($logfile)]];
		}
		return false;
	}
}