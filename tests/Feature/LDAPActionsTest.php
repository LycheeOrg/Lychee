<?php

namespace Tests\Feature;

use App\LDAP\LDAPActions;
use App\LDAP\LDAPUserData;
use App\Models\Configs;
use App\Models\User;
use Tests\LDAPTestCase;

class LDAPActionsTest extends LDAPTestCase
{
	public function testLDAPActions()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			$user_list = $ldap->get_user_list(true);
			$this->assertIsArray($user_list, 'The user list should be an array');
			$this->assertTrue(count($user_list) > 1, 'The user list should contain more than one entry');
			$user = User::query()->where('id', '>', '0')->first();
			if (!empty($user)) {
				$user->delete();
			}
			LDAPActions::update_users($user_list, false);
			$user_data = ['user' => '!__not_existant__!', 'server' => 'unknown',
				'dn' => 'cn=not0exist', 'display_name' => 'Do not know', 'email' => 'no@mail', ];
			$user = new LDAPUserData();
			$user->fromArray($user_data);
			LDAPActions::create_user_not_exist($user->user, $user);
			LDAPActions::update_users($user_list, true);
			$purge = Configs::get_value('ldap_purge', '0');
			try {
				Configs::set('ldap_purge', '1');
				LDAPActions::update_all_users();
			} finally {
				Configs::set('ldap_purge', $purge);
			}
		} finally {
			$this->done_ldap();
		}
	}
}
