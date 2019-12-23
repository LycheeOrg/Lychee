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
		if (file_exists($logfile)) {
			// we directly redirect to gallery
			http_response_code(307);
			header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
			header('Location: .');
			exit;
		}

		return false;
	}
}