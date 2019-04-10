<?php

namespace App\ModelFunctions;


use App\Configs;
use App\Logs;
use Illuminate\Support\Facades\Session;

class SessionFunctions
{


	/**
	 * Return true if the user is logged in (Admin or User)
	 * Return false if it is Guest access
	 *
	 * @return bool
	 */
	public function is_logged_in()
	{
		if (Session::get('login') === true) {
			return true;
		}
		else {
			return false;
		}
	}



	/**
	 * Sets the session values when no there is no username and password in the database.
	 * @return boolean Returns true when no login was found.
	 */
	public function noLogin()
	{

		$configs = Configs::get();

		// Check if login credentials exist and login if they don't
		if (isset($configs['username']) && $configs['username'] === '' &&
			isset($configs['password']) && $configs['password'] === '') {
				Session::put('login', true);
				Session::put('UserID', 0);
				return true;
		}

		return false;
	}
}