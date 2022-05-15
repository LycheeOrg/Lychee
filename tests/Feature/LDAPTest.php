<?php

namespace Tests\Feature;

use App\LDAP\LDAPFunctions;
use App\Models\Configs;
use Tests\TestCase;

class LDAPTest extends TestCase
{
	public function testLDAP()
	{
		$ldap = new LDAPFunctions();
		/*
		 * These tests are made without enabling LDAP as the authentication service (ldap_enabled is untouched)
		 *
		 * Scenario is as follows:
		 *
		 * 1. configure the public LDAP server (see https://www.forumsys.com/2022/05/10/online-ldap-test-server/)
		 * 2. try to verify user: gauss password: password
		 * 3. try to verify user: euler password: password
		 * 4. try to verify user: test password: password ==> should fail
		 * 5. try to verify user: gauss password: test ==> should fail
		 * 6. call LDAPFunctions::get_user_data('gauss') ==> should return an array with the data for 'gauss'
		 */

		// 1
		Configs::set('ldap_server', 'ldap.forumsys.com');
		Configs::set('ldap_user_tree', 'dc=example,dc=com');
		Configs::set('ldap_user_filter', '(uid=%{user})');
		Configs::set('ldap_bind_dn', 'cn=read-only-admin,dc=example,dc=com');
		Configs::set('ldap_bind_pw', 'password');

		// 2
		$this->assertTrue($ldap->check_pass('gauss', 'password'), 'Cannot verify user gauss:password');

		// 3
		$this->assertTrue($ldap->check_pass('euler', 'password'), 'Cannot verify user euler:password');

		// 4
		$this->assertFalse($ldap->check_pass('test', 'password'), 'Should not possible to verify user test:password');

		// 5
		$this->assertFalse($ldap->check_pass('gauss', 'test'), 'Should not possible to verify user Gauss:test');

		// 6
		$user_data = $ldap->get_user_data('gauss');

		$expected = [
			'user' => 'gauss', 'server' => 'ldap.forumsys.com', 'dn' => 'uid=gauss,dc=example,dc=com',
			'display_name' => 'Carl Friedrich Gauss', 'email' => 'gauss@ldap.forumsys.com',
		];

		$this->assertEqualsCanonicalizing($user_data->toArray(), $expected);
	}
}
