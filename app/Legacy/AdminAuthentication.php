<?php

namespace App\Legacy;

use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthentication
{
	/**
	 * Given a username, password and ip (for logging), try to log the user as admin.
	 * Returns true if succeeded, false if failed.
	 *
	 * Note that this method will only be successful "once".
	 * Upon success, the admin username is updated to a clear text value.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool true if login is successful
	 */
	public static function loginAsAdmin(string $username, string $password, string $ip): bool
	{
		/** @var User|null $adminUser */
		$adminUser = User::query()->find(1);

		// Admin User exists, so we check against it.
		if ($adminUser !== null && Hash::check($username, $adminUser->username) && Hash::check($password, $adminUser->password)) {
			Auth::login($adminUser);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

			// update the admin username so we do not need to go through here anymore.
			$adminUser->username = $username;
			$adminUser->save();

			return true;
		}

		return false;
	}
}