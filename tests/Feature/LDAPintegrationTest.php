<?php

namespace Tests\Feature;

use App\ModelFunctions\SessionFunctions;
use App\Models\Configs;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\Feature\Lib\UsersUnitTest;
use Tests\TestCase;

class LDAPintegrationTest extends TestCase
{
	protected function _debug($myDebugVar)
	{
		fwrite(STDERR, print_r($myDebugVar, true));
	}

	public function testLDAP()
	{
		$sessionFunctions = new SessionFunctions();
		$sessions_test = new SessionUnitTest($this);
		$users_test = new UsersUnitTest($this);

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
		$von_gauss = [
			'username' => 'gauss', 'fullname' => 'Carl Friedrich Gauss', 'email' => 'gauss@ldap.forumsys.com',
		];

		// 1
		$ip = '127.0.0.1';
		Configs::set('ldap_enabled', '1');
		Configs::set('ldap_server', 'ldap.forumsys.com');
		Configs::set('ldap_usertree', 'dc=example,dc=com');
		Configs::set('ldap_userfilter', '(uid=%{user})');
		Configs::set('ldap_binddn', 'cn=read-only-admin,dc=example,dc=com');
		Configs::set('ldap_bindpw', 'password');

		// 2
		$this->assertTrue($sessionFunctions->log_with_ldap('gauss', 'password', $ip), 'Cannot verify user gauss:password');
		// 3
		$this->assertTrue($sessionFunctions->log_with_ldap('euler', 'password', $ip), 'Cannot verify user euler:password');
		// 4
		$this->assertFalse($sessionFunctions->log_with_ldap('test', 'password', $ip), 'Should not possible to verify user test:password');
		// 5
		$this->assertFalse($sessionFunctions->log_with_ldap('gauss', 'test', $ip), 'Should not possible to verify user Gauss:test');
		// 6
		$this->assertTrue($sessionFunctions->log_as_user('gauss', 'password', $ip), 'Cannot verify user gauss:password');
		// 7
		$user_data = $sessionFunctions->user();
		$OK = true;
		foreach ($von_gauss as $key => $value) {
			if ($user_data[$key] != $value) {
				$OK = false;
			}
		}
		$this->assertTrue($OK, 'Userdata differ from the LDAP data');
		// 8
		$this->assertTrue($sessionFunctions->log_as_user('euler', 'password', $ip), 'Cannot verify user euler:password');
		$user_data = $sessionFunctions->user();
		$OK = true;
		foreach ($von_gauss as $key => $value) {
			if ($user_data[$key] != $value) {
				$OK = false;
			}
		}
		$this->assertFalse($OK, 'Userdata should differ from the LDAP data for gauss');
		// 9
		Configs::set('ldap_enabled', $oldconfigs['ldap_enabled']);
		Configs::set('ldap_server', $oldconfigs['ldap_server']);
		Configs::set('ldap_usertree', $oldconfigs['ldap_usertree']);
		Configs::set('ldap_userfilter', $oldconfigs['ldap_userfilter']);
		Configs::set('ldap_binddn', $oldconfigs['ldap_binddn']);
		Configs::set('ldap_bindpw', $oldconfigs['ldap_bindpw']);
	}
}
