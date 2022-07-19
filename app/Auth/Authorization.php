<?php

namespace App\Auth;

use App\Exceptions\ModelDBException;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Authorization
{
	/**
	 * If admin user does not exist or is not configured.
	 * Return false otherwise (admin exist with credentials).
	 *
	 * @return bool
	 */
	public static function isAdminNotRegistered(): bool
	{
		/** @var User|null $adminUser */
		$adminUser = User::query()->find(0);
		if ($adminUser !== null) {
			if ($adminUser->password === '' && $adminUser->username === '') {
				return true;
			}

			return false;
		}

		return self::resetAdmin();
	}

	/**
	 * TODO: Once the admin user registration is moved to the installation phase this methode can finally be removed.
	 *
	 * Login as admin temporarilly when unconfigured.
	 *
	 * @return bool true of successful
	 *
	 * @throws ModelDBException
	 */
	public static function loginAsAdminIfNotRegistered(): bool
	{
		if (self::isAdminNotRegistered()) {
			/** @var User|null $adminUser */
			$adminUser = User::query()->find(0);
			Auth::login($adminUser);
		}

		return false;
	}

	/**
	 * Given a username, password and ip (for logging), try to log the user.
	 * Returns true if succeeded, false if failed.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 */
	public static function loginAs(string $username, string $password, string $ip): bool
	{
		// We select the NON ADMIN user
		/** @var User|null $user */
		$user = User::query()->where('username', '=', $username)->first();

		if ($user !== null && Hash::check($password, $user->password)) {
			Auth::login($user);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

			return true;
		}

		return false;
	}

	/**
	 * Given a username and password, create an admin user in the database.
	 * Do note that the password is set NOT HASHED.
	 *
	 * @return bool actually always true
	 *
	 * @throws ModelDBException
	 */
	public static function resetAdmin(): bool
	{
		/** @var User $user */
		$user = User::query()->findOrNew(0);
		$user->incrementing = false; // disable auto-generation of ID
		$user->id = 0;
		$user->username = '';
		$user->password = '';
		$user->save();

		return true;
	}
}