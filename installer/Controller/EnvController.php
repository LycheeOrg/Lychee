<?php


namespace Installer\Controller;


class EnvController implements Controller
{

	/**
	 * @return array
	 */
	public function do()
	{

		if (isset($_POST['envConfig']))
		{
			file_put_contents('.env',$_POST['envConfig']);
		}

		if (file_exists('.env'))
		{
			$env = file_get_contents('.env');
			$exists = true;
		}
		else
		{

			$env = file_get_contents('.env.example');
			file_put_contents('.env',$env);
			$exists = false;
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