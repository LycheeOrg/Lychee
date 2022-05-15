<?php

namespace Tests\Feature;

use App\LDAP\LDAPFunctions;
use App\Models\Configs;
use Tests\TestCase;

class LDAPTest extends TestCase
{
	protected function _debug($myDebugVar, $label = '')
	{
		// fwrite(STDERR, $label . print_r($myDebugVar, true) . PHP_EOL);
		$msg = str_replace(PHP_EOL, ' ', print_r($myDebugVar, true));
		$msg = str_replace('  ', ' ', $msg);
		$msg = str_replace('  ', ' ', $msg);
		$msg = str_replace('  ', ' ', $msg);
		$msg = str_replace('  ', ' ', $msg);
		$msg = str_replace('  ', ' ', $msg);
		$msg = str_replace('  ', ' ', $msg);
		error_log($label . "'" . trim($msg) . "'");
	}

	public function testLDAP()
	{
		$ldap = new LDAPFunctions();
		/*
		 * These tests are made without enabling LDAP as the authenication service (ldap_enabled is untouched)
		 *
		 * Scenario is as follows:
		 *
		 * 1. configure the public LDAP server (see https://www.forumsys.com/2022/05/10/online-ldap-test-server/)
		 * 2. call LDAPFunctions::OpenLDAP to check if a connection could be established
		 * 3. try to verify user: gauss password: password
		 * 4. try to verify user: euler password: password
		 * 5. try to verify user: test password: password ==> should fail
		 * 6. try to verify user: gauss password: test ==> should fail
		 * 7. call LDAPFunctions::get_user_data('gauss') ==> should return an array with the data for 'gauss'
		 */

		// 1
		Configs::set('ldap_server', 'ldap.forumsys.com');
		Configs::set('ldap_usertree', 'dc=example,dc=com');
		Configs::set('ldap_userfilter', '(uid=%{user})');
		Configs::set('ldap_binddn', 'cn=read-only-admin,dc=example,dc=com');
		Configs::set('ldap_bindpw', 'password');

		// 2

		$this->assertTrue($ldap->open_LDAP(), 'Connection to LDAP test server failed');
		$this->_debug($ldap->LDAP_bind(), 'ldap_bind: ');
		$user_data = $ldap->get_user_data('gauss');
		$this->_debug($user_data, 'get_user_data(gauss): ');
		if ($user_data) {
			$this->_debug($ldap->LDAP_bind($user_data->dn, 'password'), 'ldap_bind(pd,password): ');
		}
		// 3
		$this->assertTrue($ldap->check_pass('gauss', 'password'), 'Cannot verify user gauss:password');

		// 4
		$this->assertTrue($ldap->check_pass('euler', 'password'), 'Cannot verify user euler:password');

		// 5
		$this->assertFalse($ldap->check_pass('test', 'password'), 'Should not possible to verify user test:password');

		// 6
		$this->assertFalse($ldap->check_pass('gauss', 'test'), 'Should not possible to verify user Gauss:test');

		// 7
		$user_data = $ldap->get_user_data('gauss');

		$expected = [
			'user' => 'gauss', 'server' => 'ldap.forumsys.com', 'dn' => 'uid=gauss,dc=example,dc=com',
			'fullname' => 'Carl Friedrich Gauss', 'email' => 'gauss@ldap.forumsys.com',
		];

		$this->assertEqualsCanonicalizing($user_data->toArray(), $expected);
	}
}
