<?php

namespace App\Auth;

use App\Legacy\Legacy;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class Authorization extends Auth
{
	public static function id(): int|null
	{
		return is_int(parent::id()) ? parent::id() : null;
	}

	public static function isAdmin(): bool
	{
		return parent::user()?->isAdmin() === true;
	}

	/**
	 * @return bool
	 */
	public static function canUpload(): bool
	{
		$user = parent::user();

		return $user?->id === 0 || $user?->may_upload === true;
	}

	/**
	 * Returns true if the user matches the id or if is admin.
	 *
	 * @param int $id to check
	 *
	 * @return bool
	 */
	public static function isCurrentOrAdmin(int $id)
	{
		return self::id() === 0 || self::id() === $id;
	}

	/**
	 * Sets the session values when no there is no username and password in the database.
	 *
	 * @return bool returns true when no login was found
	 */
	public static function noLogin(): bool
	{
		/** @var User|null $adminUser */
		$adminUser = User::query()->find(0);
		if ($adminUser !== null && $adminUser->password === '' && $adminUser->username === '') {
			parent::login($adminUser);

			return true;
		}

		return Legacy::noLogin();
	}

	/**
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	public static function logout()
	{
		parent::logout();
		Session::flush();
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
	public static function logAsUser(string $username, string $password, string $ip): bool
	{
		// We select the NON ADMIN user
		/** @var User|null $user */
		$user = User::query()->where('username', '=', $username)->where('id', '>', '0')->first();

		if ($user !== null && Hash::check($password, $user->password)) {
			parent::login($user);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

			return true;
		}

		return false;
	}

	/**
	 * Given a username, password and ip (for logging), try to log the user as admin.
	 * Returns true if succeeded, false if failed.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 */
	public static function logAsAdmin(string $username, string $password, string $ip): bool
	{
		/** @var User|null $adminUser */
		$adminUser = User::query()->find(0);

		if ($adminUser !== null) {
			// Admin User exist, so we check against it.
			if (Hash::check($username, $adminUser->username) && Hash::check($password, $adminUser->password)) {
				parent::login($adminUser);
				Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

				return true;
			}

			return false;
		}
		// Admin User does not exist yet, so we use the Legacy.
		return Legacy::log_as_admin($username, $password, $ip);
	}
}