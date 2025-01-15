<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy;

use App\Metadata\Versions\InstalledVersion;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
		$installed_version = new InstalledVersion();
		$db_version_number = $installed_version->getVersion();

		// For version up to 4.0.8 the admin password is stored in the settings
		/** @codeCoverageIgnore */
		if ($db_version_number->toInteger() <= 40008) {
			// @codeCoverageIgnoreStart
			return self::logAsAdminFromConfig($username, $password, $ip);
			// @codeCoverageIgnoreEnd
		}

		// For version up to 4.6.3
		$admin_id = $db_version_number->toInteger() <= 40603 ? 0 : 1;
		// Note there is a small edge case where a user could be at version 4.6.3 AND having already bumped the ID.
		// We consider this risk to be too small to actually mitigate it.

		/** @var User|null $adminUser */
		$adminUser = User::query()->find($admin_id);

		// Admin User exists, so we check against it.
		if ($adminUser !== null && Hash::check($username, $adminUser->username) && Hash::check($password, $adminUser->password)) {
			Auth::login($adminUser);
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . ' User (' . $username . ') has logged in from ' . $ip);

			// update the admin username so we do not need to go through here anymore.
			$adminUser->username = $username;
			$adminUser->save();

			return true;
		}

		return false;
	}

	/**
	 * This is only applicable if we are up to version 4.0.8 in which the refactoring of admin append.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore
	 */
	public static function logAsAdminFromConfig(string $username, string $password, string $ip): bool
	{
		$username_hash = Configs::getValueAsString('username');
		$password_hash = Configs::getValueAsString('password');

		if (Hash::check($username, $username_hash) && Hash::check($password, $password_hash)) {
			// Prior version 4.6.3 we are using ID 0 as admin
			// We create admin at ID 0 because the 2022_12_10_183251_increment_user_i_ds will be taking care to push it to 1.
			/** @var User $adminUser */
			$adminUser = User::query()->findOrNew(0);
			$adminUser->username = $username;
			$adminUser->password = Hash::make($password);
			$adminUser->save();

			Auth::login($adminUser);
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . ' User (' . $username . ') has logged in from ' . $ip . ' (legacy)');

			return true;
		}

		return false;
	}
}