<?php


namespace Installer\Controller;


final class WelcomeController implements Controller
{

	/**
	 * @return array
	 */
	public function do()
	{
		return ['toto'=> 'tata'];
	}



	/**
	 * @return string
	 */
	public function view()
	{
		return 'Welcome';
	}
}