<?php

namespace App\Legacy;

use App\Actions\User\ResetAdmin;
use App\Exceptions\ModelDBException;
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
		$adminUser = User::query()->find(0);

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

	/**
	 * Checks whether the admin is unconfigured.
	 * The method is not side-effect free.
	 * If the admin user happens to not exist at all, the method creates an unconfigured admin.
	 *
	 * @return bool
	 *
	 * @throws ModelDBException
	 */
	public static function isAdminNotRegistered(): bool
	{
		/** @var User|null $adminUser */
		$adminUser = User::query()->find(0);
		if ($adminUser !== null) {
			return $adminUser->password === '' || $adminUser->username === '';
		}
		(new ResetAdmin())->do();

		return true;
	}

	/**
	 * TODO: Once the admin user registration is moved to the installation phase this method can finally be removed.
	 *
	 * Login as admin temporarily when unconfigured.
	 *
	 * @return bool true if successful
	 *
	 * @throws ModelDBException
	 */
	public static function loginAsAdminIfNotRegistered(): bool
	{
		if (self::isAdminNotRegistered()) {
			/** @var User|null $adminUser */
			$adminUser = User::query()->find(0);
			Auth::login($adminUser);

			return true;
		}

		return false;
	}
}