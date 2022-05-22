<?php

namespace App\LDAP;

use App\Actions\User\Create;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;

/**
 * Class LDAPUpdateJobs.
 *
 * This class manages the update between the database and LDAP.
 */
class LDAPActions
{
	/**
	 * Create a user in the database if it does not exist already.
	 */
	public static function create_user_not_exist(string $username, FixedArray $userData): void
	{
		$user = User::query()->where('username', '=', $username)->first();
		if ($user == null) {
			Logs::debug(__METHOD__, __LINE__, 'Create User (not exist): ' . $username);
			$create = resolve(Create::class);
			// password is set to null for LDAP users
			$create->do($username, null, false, true, $userData->email, $userData->display_name);
		}
	}

	public static function update_user(string $username, FixedArray $userData): void
	{
		$user = User::query()->where('username', '=', $username)->where('id', '>', '0')->first();
		if (($user != null) && (($user->display_name != $userData->display_name) || ($user->email != $userData->email))) {
			Logs::debug(__METHOD__, __LINE__, 'Update User: ' . $username);
			$user->email = $userData->email;
			$user->display_name = $userData->display_name;
			$user->save();
		}
	}

	public static function update_users(array $user_list, bool $purge = false): void
	{
		Logs::debug(__METHOD__, __LINE__, 'Update Users');
		foreach ($user_list as $userData) {
			$user = User::query()->where('username', '=', $userData->user)->first();
			if ($user == null) {
				LDAPActions::create_user_not_exist($userData->user, $userData);
			} else {
				LDAPActions::update_user($userData->user, $userData);
			}
		}
		if ($purge) {
			Logs::debug(__METHOD__, __LINE__, 'Purge Users');
			// Purging of not existing users
			$users = User::query()->where('id', '>', '0')->get();
			foreach ($users as $user) {
				if (!array_key_exists($user->username, $user_list)) {
					Logs::debug(__METHOD__, __LINE__, 'Purge User: ' . $user->username);
					$user->delete();
				}
			}
		}
	}

	public static function update_all_users(?bool $purge = null): void
	{
		$ldap = new LDAPFunctions();
		if (is_null($purge)) {
			$purge = Configs::get_value('ldap_purge', '0') == '1';
		}
		LDAPActions::update_users($ldap->get_user_list(true), $purge);
	}
}
