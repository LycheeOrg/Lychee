<?php

namespace App\Auth;

use App\Legacy\Legacy;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Authorization
{
	/**
	 * Forward the check call to Auth.
	 * Check if user is logged in.
	 *
	 * @return bool
	 */
	public static function check(): bool
	{
		return Auth::check();
	}

	/**
	 * @param Authenticatable $user
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	public static function login(Authenticatable $user): void
	{
		Auth::login($user); // returns void anyway.
	}

	/**
	 * Forward the id call to Auth
	 * Returns id if logged in, null otherwise.
	 *
	 * @return int|null
	 */
	public static function id(): int|null
	{
		return is_int(Auth::id()) ? Auth::id() : null;
	}

	/**
	 * Forward the user call to Auth.
	 * Returns current user if logged in, null otherwise.
	 *
	 * @return User|null
	 *
	 * @throws InvalidArgumentException
	 * @throws BadRequestException
	 */
	public static function user(): User|null
	{
		return Auth::user();
	}

	public static function isAdmin(): bool
	{
		return Auth::user()?->isAdmin() === true;
	}

	/**
	 * Check if User can upload.
	 *
	 * @return bool
	 */
	public static function canUpload(): bool
	{
		$user = Auth::user();

		return $user?->id === 0 || $user?->may_upload === true;
	}

	/**
	 * Forwards the loginUsingId to Auth.
	 * Log in user using its id, return User if successful.
	 *
	 * @param int $id
	 *
	 * @return Authenticatable|false
	 *
	 * @throws RuntimeException
	 */
	public static function loginUsingId(int $id): Authenticatable|false
	{
		return Auth::loginUsingId($id);
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
	public static function isAdminNotConfigured(): bool
	{
		/** @var User|null $adminUser */
		$adminUser = User::query()->find(0);
		if ($adminUser !== null && $adminUser->password === '' && $adminUser->username === '') {
			Auth::login($adminUser);

			return true;
		}

		return Legacy::isAdminNotConfigured();
	}

	/**
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	public static function logout()
	{
		Auth::logout();
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
			Auth::login($user);
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
				Auth::login($adminUser);
				Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip);

				return true;
			}

			return false;
		}
		// Admin User does not exist yet, so we use the Legacy.
		return Legacy::log_as_admin($username, $password, $ip);
	}
}