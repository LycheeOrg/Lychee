<?php

namespace Installer\Controller;

class EnvController implements Controller
{
	/**
	 * @return array
	 */
	public function do()
	{
		$env = '';
		$exists = false;

		if (file_exists('.env')) {
			$env = file_get_contents('.env');
			$exists = true;
		} else {
			$env = file_get_contents('.env.example');
			$exists = false;
		}

		if (isset($_POST['envConfig'])) {
			$env = str_replace("\r", '', $_POST['envConfig']);
			file_put_contents('.env', $env, LOCK_EX);
			$exists = true;
		}

		return ['env' => $env, 'exists' => $exists];
	}

	/**
	 * @return string
	 */
	public function view()
	{
		return 'Env';
	}
}