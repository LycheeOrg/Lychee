<?php

namespace App\Auth;

use App\Exceptions\ModelDBException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
	 * Login as admin temporarily when unconfigured.
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