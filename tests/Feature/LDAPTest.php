<?php

namespace Tests\Feature;

use App\LDAP\LDAPFunctions;
use App\Models\Configs;
use Tests\TestCase;

class LDAPTest extends TestCase
{
	public const TESTUSER = 'gauss';
	public const TESTUSER_PW = 'password';
	public const TESTUSER2 = 'euler';
	public const TESTUSER2_PW = 'password';
	public const TESTUSER_DN = 'uid=gauss,dc=example,dc=com';
	public const TESTUSER_CN = 'Carl Friedrich Gauss';
	public const TESTUSER_EMAIL = 'gauss@ldap.forumsys.com';
	public const TESTUSER_FILTER = '(uid=gauss)';

	public const SERVER = 'ldap.forumsys.com';
	public const USER_TREE = 'dc=example,dc=com';
	public const USER_FILTER = '(uid=%{user})';
	public const BIND_DN = 'cn=read-only-admin,dc=example,dc=com';
	public const BIND_PW = 'password';

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
		$ldap = new LDAPFunctions();
		/*
		 * These tests are made without enabling LDAP as the authenication service (ldap_enabled is untouched)
		 *
		 * Scenario is as follows:
		 *
		 * 1. configure the public LDAP server (see https://www.forumsys.com/2022/05/10/online-ldap-test-server/)
		 * 2. call LDAPFunctions::test_openLDAP etc. to check if a connection could be established
		 * 3. try to verify user: gauss password: password
		 * 4. try to verify user: euler password: password
		 * 5. try to verify user: test password: password ==> should fail
		 * 6. try to verify user: gauss password: test ==> should fail
		 * 7. call LDAPFunctions::get_user_data(TESTUSER) ==> should return an array with the data for TESTUSER
		 */
		$test_user = [
			'user' => self::TESTUSER, 'server' => self::SERVER, 'dn' => self::TESTUSER_DN,
			'display_name' => self::TESTUSER_CN, 'email' => self::TESTUSER_EMAIL,
		];

		// 1
		Configs::set('ldap_server', self::SERVER);
		Configs::set('ldap_user_tree', self::USER_TREE);
		Configs::set('ldap_user_filter', self::USER_FILTER);
		Configs::set('ldap_bind_dn', self::BIND_DN);
		Configs::set('ldap_bind_pw', self::BIND_PW);

		// 2
		$this->assertTrue($ldap->test_open_LDAP(), 'Connection to LDAP test server failed');

		// Call get_user_data() befor LDAP_bind() to check if the automatic binding is working
		// This also works with an anonymous binding LDAP_Server, verifyable by using a local server
		$user_data = $ldap->get_user_data(self::TESTUSER);

		$this->assertTrue($ldap->test_LDAP_bind(), 'ldap_bind has failed');
		$SR = $ldap->test_LDAP_search(self::USER_TREE, self::TESTUSER_FILTER, 'sub');
		$this->assertFalse($SR['count'] == 0, 'LDAP_search got no result');
		$this->assertFalse($SR['count'] > 1, 'LDAP_search got more than one result, should be one');
		$user_data = $ldap->get_user_data(self::TESTUSER);
		$this->assertEqualsCanonicalizing($user_data->toArray(), $test_user);

		if ($user_data) {
			$this->assertTrue($ldap->test_LDAP_bind($user_data->dn, self::TESTUSER_PW), 'Cannot ldap_bind to user TESTUSER');
		}
		// 3
		$this->assertTrue($ldap->check_pass(self::TESTUSER, self::TESTUSER_PW), 'Cannot verify user TESTUSER');

		// 4
		$this->assertTrue($ldap->check_pass(self::TESTUSER2, self::TESTUSER2_PW), 'Cannot verify user TESTUSER2');

		// 5
		$this->assertFalse($ldap->check_pass('test', 'password'), 'Should not possible to verify user test:TESTUSER_PW');

		// 6
		$this->assertFalse($ldap->check_pass(self::TESTUSER, 'test'), 'Should not possible to verify user TESTUSER:test');

		// 7
		$user_data = $ldap->get_user_data(self::TESTUSER);
		$this->assertEqualsCanonicalizing($user_data->toArray(), $test_user);
	}
}
