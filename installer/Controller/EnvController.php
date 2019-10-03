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
			$errors = false;
		}
		else
		{

			$env = file_get_contents('.env.example');
			file_put_contents('.env',$env);
			$errors = true;
		}

		return ['env' => $env, 'errors' => $errors];
	}



	/**
	 * @return string
	 */
	public function view()
	{
		return 'Env';
	}
}