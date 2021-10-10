<?php

namespace App\Legacy;

use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * Stuff we need to delete in the future.
 */
class Legacy
{
	/**
	 * @throws QueryBuilderException
	 */
	public static function resetAdmin(): void
	{
		try {
			Configs::query()
				->where('key', '=', 'username')
				->orWhere('key', '=', 'password')
				->update(['value' => '']);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * @throws InvalidConfigOption
	 */
	public static function SetPassword(string $hashedUsername, string $hashedPassword): bool
	{
		$configs = Configs::get();

		if (Configs::get_value('version', '040000') < '040008') {
			if ($configs['password'] === '' && $configs['username'] === '') {
				Configs::set('username', $hashedUsername);
				Configs::set('password', $hashedPassword);

				return true;
			}
		}

		return false;
	}

	public static function noLogin(): bool
	{
		// LEGACY STUFF
		$configs = Configs::get();

		if (Configs::get_value('version', '040000') <= '040008') {
			// Check if login credentials exist and login if they don't
			if (
				isset($configs['username']) && $configs['username'] === '' &&
				isset($configs['password']) && $configs['password'] === ''
			) {
				Session::put('login', true);
				Session::put('UserID', 0);

				return true;
			}
		}

		return false;
	}

	public static function log_as_admin(string $username, string $password, string $ip): bool
	{
		$configs = Configs::get();

		if (Hash::check($username, $configs['username']) && Hash::check($password, $configs['password'])) {
			Session::put('login', true);
			Session::put('UserID', 0);
			Logs::notice(__METHOD__, __LINE__, 'User (' . $username . ') has logged in from ' . $ip . ' (legacy)');

			return true;
		}

		return false;
	}
}
