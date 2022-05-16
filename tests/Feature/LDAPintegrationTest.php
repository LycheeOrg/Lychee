<?php

namespace Tests\Feature;

use App\ModelFunctions\SessionFunctions;
use App\Models\Configs;
use Tests\TestCase;

class LDAPintegrationTest extends TestCase
{
	protected function _debug($myDebugVar, $label = '')
	{
		$msg = str_replace(PHP_EOL, ' ', print_r($myDebugVar, true));
		while (str_contains($msg, '  ')) {
			$msg = str_replace('  ', ' ', $msg);
		}
		error_log($label . "'" . trim($msg) . "'");
	}

	public function testLDAP()
	{
		$sessionFunctions = new SessionFunctions();

		$oldconfigs = Configs::get();
		/*
		 * These tests are made with enabling LDAP as the authenication service (ldap_enabled is set)
		 * These tests verify the interface between the session functions and the LDAP functions
		 *
		 * Scenario is as follows:
		 *
		 * 1. configure the public LDAP server (see https://www.forumsys.com/2022/05/10/online-ldap-test-server/)
		 * 2. try to verify user: gauss password: password via log_with_ldap()
		 * 3. try to verify user: euler password: password via log_with_ldap()
		 * 4. try to verify user: test password: password via log_with_ldap() ==> should fail
		 * 5. try to verify user: gauss password: test via log_with_ldap() ==> should fail
		 * 6. try to verify user gaus via log_as_user()
		 * 7. Verify the return values from user()
		 * 8. Verify that the return values from user() for euler do not match gauss.
		 * 8. Restore original config values
		 */
		$test_user = [
			'username' => LDAPTest::TESTUSER, 'display_name' => LDAPTest::TESTUSER_CN, 'email' => LDAPTest::TESTUSER_EMAIL,
		];

		// 1
		$ip = '127.0.0.1';
		Configs::set('ldap_enabled', '1');
		Configs::set('ldap_server', LDAPTest::SERVER);
		Configs::set('ldap_user_tree', LDAPTest::USER_TREE);
		Configs::set('ldap_user_filter', LDAPTest::USER_FILTER);
		Configs::set('ldap_bind_dn', LDAPTest::BIND_DN);
		Configs::set('ldap_bind_pw', LDAPTest::BIND_PW);

		// 2
		$this->assertTrue($sessionFunctions->log_with_ldap(LDAPTest::TESTUSER, LDAPTest::TESTUSER_PW, $ip), 'Cannot verify testuser');
		// 3
		$this->assertTrue($sessionFunctions->log_with_ldap(LDAPTest::TESTUSER2, LDAPTest::TESTUSER2_PW, $ip), 'Cannot verify testuser2');
		// 4
		$this->assertFalse($sessionFunctions->log_with_ldap('test', 'password', $ip), 'Should not possible to verify an unknown user');
		// 5
		$this->assertFalse($sessionFunctions->log_with_ldap(LDAPTest::TESTUSER, 'test', $ip), 'Should not possible to verify testuser (wrong pw)');
		// 6
		$this->assertTrue($sessionFunctions->log_as_user(LDAPTest::TESTUSER, LDAPTest::TESTUSER_PW, $ip), 'Cannot verify testuser');
		// 7
		$user_data = $sessionFunctions->user();
		$OK = true;
		foreach ($test_user as $key => $value) {
			if ($user_data[$key] != $value) {
				$OK = false;
			}
		}
		$this->assertTrue($OK, 'Userdata differ from the LDAP data');
		// 8
		$this->assertTrue($sessionFunctions->log_as_user(LDAPTest::TESTUSER2, LDAPTest::TESTUSER2_PW, $ip), 'Cannot verify testuser2');
		$user_data = $sessionFunctions->user();
		$OK = true;
		foreach ($test_user as $key => $value) {
			if ($user_data[$key] != $value) {
				$OK = false;
			}
		}
		$this->assertFalse($OK, 'Userdata should differ from the LDAP data for gauss');
		// 9
		Configs::set('ldap_enabled', $oldconfigs['ldap_enabled']);
		Configs::set('ldap_server', $oldconfigs['ldap_server']);
		Configs::set('ldap_user_tree', $oldconfigs['ldap_user_tree']);
		Configs::set('ldap_user_filter', $oldconfigs['ldap_user_filter']);
		Configs::set('ldap_bind_dn', $oldconfigs['ldap_bind_dn']);
		Configs::set('ldap_bind_pw', $oldconfigs['ldap_bind_pw']);
	}
}
