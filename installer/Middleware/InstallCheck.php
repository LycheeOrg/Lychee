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
			// we directly redirect to gallery
			header("Location: /gallery");
			exit;
		}
		return false;
	}
}