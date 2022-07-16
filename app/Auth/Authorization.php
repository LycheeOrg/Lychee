<?php

namespace App\Auth;

use App\Legacy\Legacy;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
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
	public static function idOrNull(): int|null
	{
		return is_int(Auth::id()) ? Auth::id() : null;
	}

	/**
	 * Return the user Id if logged in.
	 * Fail otherwise.
	 *
	 * @return int
	 *
	 * @throws AuthenticationException
	 */
	public static function idOrFail(): int
	{
		/** @var User $user */
		$user = Auth::authenticate();

		return $user->id;
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
	public static function userOrNull(): User|null
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user;
	}

	/**
	 * Return current user if logged in.
	 * Fail otherwise.
	 *
	 * @return User
	 *
	 * @throws AuthenticationException
	 */
	public static function userOrFail(): User
	{
		/** @var User $user */
		$user = Auth::authenticate();

		return $user;
	}

	/**
	 * Return true if user is admin.
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException
	 * @throws BadRequestException
	 */
	public static function isAdmin(): bool
	{
		/** @var User|null $user */
		$user = Auth::user();

		return $user?->isAdmin() === true;
	}

	/**
	 * Check if User can upload.
	 *
	 * @return bool
	 */
	public static function canUpload(): bool
	{
		/** @var User $user */
		$user = Auth::authenticate();

		return $user->id === 0 || $user->may_upload;
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
		return self::idOrNull() === 0 || self::idOrNull() === $id;
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
		if ($adminUser !== null) {
			if ($adminUser->password === '' && $adminUser->username === '') {
				Auth::login($adminUser);

				return true;
			}

			return false;
		}

		return self::createAdminAndLogin('', '');
	}

	/**
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	public static function logout(): void
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
		return self::createAdminAndLogin('', '');
	}

	/**
	 * Givne a username and password, create an admin user in the database.
	 *
	 * @param mixed $username
	 * @param mixed $password
	 *
	 * @return bool actually always true
	 */
	private static function createAdminAndLogin($username, $password): bool
	{
		/** @var User */
		$user = User::query()->findOrNew(0);
		$user->incrementing = false; // disable auto-generation of ID
		$user->id = 0;
		$user->username = $username;
		$user->password = $password;
		$user->save();
		Auth::login($user);

		return true;
	}
}