<?php

namespace Tests\Feature;

use App\LDAP\LDAPActions;
use App\LDAP\LDAPFunctions;
use App\Models\Configs;
use Tests\TestCase;

class LDAPActionsTest extends TestCase
{
	protected function _debug($myDebugVar, $label = '', $oneline = true)
	{
		$msg = print_r($myDebugVar, true);
		if ($oneline) {
			$msg = str_replace(PHP_EOL, ' ', $msg);
			while (str_contains($msg, '  ')) {
				$msg = str_replace('  ', ' ', $msg);
			}
		}
		error_log($label . "'" . trim($msg) . "'");
	}

	public function testLDAPActions()
	{
		$ldap = new LDAPFunctions();

		$test_user = [
			'user' => LDAPTest::TESTUSER, 'display_name' => LDAPTest::TESTUSER_CN, 'email' => LDAPTest::TESTUSER_EMAIL,
		];
		$oldconfigs = Configs::get();
		// 1
		Configs::set('ldap_server', LDAPTest::SERVER);
		Configs::set('ldap_user_tree', LDAPTest::USER_TREE);
		Configs::set('ldap_user_filter', LDAPTest::USER_FILTER);
		Configs::set('ldap_bind_dn', LDAPTest::BIND_DN);
		Configs::set('ldap_bind_pw', LDAPTest::BIND_PW);

		// 2
		$this->assertTrue($ldap->test_open_LDAP(), 'Connection to LDAP test server failed');

		$user_list = $ldap->get_user_list(true);
		$this->assertIsArray($user_list, 'The user list should be an array');
		$this->assertTrue(count($user_list) > 1, 'The user list should contain more than one entry');
		LDAPActions::update_users($user_list, false);
		LDAPActions::update_users($user_list, true);

		Configs::set('ldap_enabled', $oldconfigs['ldap_enabled']);
		Configs::set('ldap_server', $oldconfigs['ldap_server']);
		Configs::set('ldap_user_tree', $oldconfigs['ldap_user_tree']);
		Configs::set('ldap_user_filter', $oldconfigs['ldap_user_filter']);
		Configs::set('ldap_bind_dn', $oldconfigs['ldap_bind_dn']);
		Configs::set('ldap_bind_pw', $oldconfigs['ldap_bind_pw']);
	}
}
