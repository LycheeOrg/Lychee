<?php

namespace App\Facades;

use App\Models\User;
use Illuminate\Support\Facades\Facade;

/**
 * Class AccessControl.
 *
 * Provides access to methods of {@link \App\ModelFunctions\SessionFunctions}
 * in a static way.
 *
 * @internal keep the list of documented method in sync with
 * {@link \App\ModelFunctions\SessionFunctions}
 *
 * @method static void log_as_id(int $userId)
 * @method static bool is_logged_in()
 * @method static bool is_admin()
 * @method static bool can_upload()
 * @method static int id()
 * @method static User accessUserData()
 * @method static User user()
 * @method static bool is_current_user_or_admin(int $userId)
 * @method static void login(User $user)
 * @method static bool noLogin()
 * @method static bool log_as_user(string $username, string $password, string $ip)
 * @method static bool log_as_admin(string $username, string $password, string $ip)
 * @method static void logout()
 */
class AccessControl extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'AccessControl';
	}
}
