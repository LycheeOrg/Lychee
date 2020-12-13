<?php

namespace App\Legacy;

use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * Stuff we need to delete in the future.
 */
class Legacy
{
	public static function resetAdmin(): void
	{
		Configs::where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['value' => '']);
	}

	public static function SetPassword($request)
	{
		$configs = Configs::get();
		if (Configs::get('version', '040000') < '040008') {
			if ($configs['password'] === '' && $configs['username'] === '') {
				Configs::set('username', bcrypt($request['username']));
				Configs::set('password', bcrypt($request['password']));

				return true;
			}
		}

		return false;
	}

	public static function noLogin(): bool
	{
		// LEGACY STUFF
		$configs = Configs::get();

		if (Configs::get('version', '040000') < '040008') {
			// Check if login credentials exist and login if they don't
			if (
				isset($configs['username']) && $configs['username'] === '' &&
				isset($configs['password']) && $configs['password'] === ''
			) {
				// Session::put('login', true);
				// Session::put('UserID', 0);
				Auth::loginUsingId(0);

				return true;
			}
		}

		return false;
	}

	public static function log_as_admin(string $username, string $password, string $ip): bool
	{
		$configs = Configs::get();

		if (Hash::check($username, $configs['username']) && Hash::check($password, $configs['password'])) {
			// Session::put('login', true);
			// Session::put('UserID', 0);
			Auth::loginUsingId(0);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip . ' (legacy)');

			return true;
		}

		return false;
	}
}
