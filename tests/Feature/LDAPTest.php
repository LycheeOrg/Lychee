<?php

namespace Tests\Feature;

use Tests\Feature\Lib\LDAPTestFunctions;
use Tests\LDAPTestCase;

class LDAPTest extends LDAPTestCase
{
	protected $test_user = [
		'user' => self::TESTUSER, 'server' => self::SERVER, 'dn' => self::TESTUSER_DN,
		'display_name' => self::TESTUSER_CN, 'email' => self::TESTUSER_EMAIL,
	];

	public function testLDAPInternFunctions()
	{
		$ldap = $this->get_ldap();
		try {
			$this->assertTrue($ldap->LDAP_open(), 'Connection to LDAP test server failed');

			$ldap->LDAP_get_option(LDAP_OPT_PROTOCOL_VERSION, $option);
			$this->assertEquals(3, $option, 'LDAP Version should be 3');
			$ldap->LDAP_get_option(LDAP_OPT_SIZELIMIT, $size_limit);
			$ldap->LDAP_set_option(LDAP_OPT_SIZELIMIT, '1000');
			$ldap->LDAP_get_option(LDAP_OPT_SIZELIMIT, $new_size);
			$this->assertEquals(1000, $new_size, 'Option cannot be reset');
			$ldap->LDAP_set_option(LDAP_OPT_SIZELIMIT, $size_limit);
			$ldap->LDAP_get_option(LDAP_OPT_SIZELIMIT, $new_sl);
			$this->assertEquals(0, $new_sl, 'Option cannot be reset');
			$Filter = LDAPTestFunctions::LDAP_makeFilter('%{uid} = %{start} test%{inc} %{end}', ['uid' => 'gauss', 'start' => '{', 'end' => '}', 'inc' => '++', 'dec' => '--']);
			$this->assertEquals('gauss = { test++ }', $Filter, 'LDAP_makeFilter could not make the filter');
			$Filter = LDAPTestFunctions::LDAP_filterEscape("/\x03 \x05 \x08 (test) \\/");
			$this->assertEquals('/\03 \05 \08 \28test\29 \5c/', $Filter, 'LDAP_filterEscape could not escape properly');

			$this->assertTrue($ldap->LDAP_close(), 'Connection to LDAP server cannot be closed');
		} finally {
			$this->done_ldap();
		}
	}

	// Test LDAP bound level handling
	public function testLDAPBoundLevel()
	{
		$ldap = $this->get_ldap();
		try {
			$this->assertTrue($ldap->LDAP_open(), 'Connection to LDAP test server failed');
			// The bound level after open_LDAP() is 2 if open_LDAP() does already the binding with the LDAP server.
			$this->assertEquals(2, $ldap->LDAP_get_bound(), 'Bound level should be SUPERUSER');

			// Call get_user_data() befor LDAP_bind() to check if the automatic binding is working
			// This also works with an anonymous binding LDAP_Server, verifyable by using a local server
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertEqualsCanonicalizing($user_data->toArray(), $this->test_user);
			$this->assertEquals(2, $ldap->LDAP_get_bound(), 'Bound level should be SUPERUSER');

			$this->assertTrue($ldap->LDAP_bind(), 'ldap_bind has failed');
			$this->assertEquals(2, $ldap->LDAP_get_bound(), 'Bound level should be SUPERUSER');

			$this->assertTrue($ldap->LDAP_bind(self::TESTUSER_DN, self::TESTUSER_PW), 'ldap_bind has failed for TESTUSER_DN:TESTUSER_PW');
			$this->assertEquals(1, $ldap->LDAP_get_bound(), 'Bound level should be USER');

			$this->expectException(\App\Exceptions\LDAPException::class, 'Missing Exception from ldap_bind when called with UNKNOWN_USER:password');
			$ldap->LDAP_bind(self::UNKNOWN_USER, 'password');
			$this->assertEquals(-1, $ldap->LDAP_get_bound(), 'Bound level should be UNBOUND');
			$this->assertTrue($ldap->LDAP_close(), 'Connection to LDAP server cannot be closed');
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPSearch()
	{
		$ldap = $this->get_ldap();
		try {
			$this->assertTrue($ldap->LDAP_open(), 'Connection to LDAP test server failed');

			$SR = $ldap->LDAP_search(self::USER_TREE, self::TESTUSER_FILTER, 'sub');

			$this->assertFalse($SR['count'] == 0, 'LDAP_search got no result');
			$this->assertFalse($SR['count'] > 1, 'LDAP_search got more than one result, should be one');
			$SR = $ldap->LDAP_search(self::USER_TREE, '(objectClass=*)', 'base');
			$this->assertTrue($SR['count'] > 0, 'LDAP_search scope base should return at least one result');

			$SR = $ldap->LDAP_search(self::USER_TREE, '(objectClass=*)', 'one');
			$this->assertTrue($SR['count'] > 0, 'LDAP_search scope base should return at least one result');

			$this->assertTrue($ldap->LDAP_close(), 'Connection to LDAP server cannot be closed');
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPCheck()
	{
		$ldap = $this->get_ldap();
		try {
			/*
			 * These tests are made without enabling LDAP as the authenication service (ldap_enabled is untouched)
			 *
			 * Scenario is as follows:
			 *
			 * 3. try to verify user: gauss password: password
			 * 4. try to verify user: euler password: password
			 * 5. try to verify user: test password: password ==> should fail
			 * 6. try to verify user: gauss password: test ==> should fail
			 * 7. call LDAPFunctions::get_user_data(TESTUSER) ==> should return an array with the data for TESTUSER
			 */

			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertEqualsCanonicalizing($user_data->toArray(), $this->test_user);

			if ($user_data) {
				$this->assertTrue($ldap->LDAP_bind($user_data->dn, self::TESTUSER_PW), 'Cannot ldap_bind to user TESTUSER');
			}
			// 3
			$this->assertTrue($ldap->check_pass(self::TESTUSER, self::TESTUSER_PW), 'Cannot verify user TESTUSER');
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertTrue(is_a($user_data, 'App\LDAP\LDAPUserData'), 'TESTUSER is unknown');

			// 4
			$user_data = $ldap->get_user_data(self::TESTUSER2);
			$this->assertTrue(is_a($user_data, 'App\LDAP\LDAPUserData'), 'TESTUSER2 is unknown');
			$this->assertTrue($ldap->check_pass(self::TESTUSER2, self::TESTUSER2_PW), 'Cannot verify user TESTUSER2');

			// 5
			$this->assertFalse($ldap->check_pass(self::UNKNOWN_USER, 'password'), 'Should not possible to verify the UNKNOWN_USER:TESTUSER_PW');

			$this->assertFalse($ldap->check_pass('test', '08154711'), 'Should not be possible to verify user test:08154711');
			// 6
			$this->assertFalse($ldap->check_pass(self::TESTUSER, '08154711'), 'Should not possible to verify user TESTUSER:08154711');

			// 7
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertTrue(is_a($user_data, 'App\LDAP\LDAPUserData'), 'TESTUSER is unknown');
			$this->assertEqualsCanonicalizing($user_data->toArray(), $this->test_user);
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPCheckUnknown()
	{
		$ldap = $this->get_ldap();
		try {
			// Ensures that UNKNOWN_USER does not have an account on the LDAP test server
			$user_list = $ldap->get_user_list(true);
			$this->assertIsArray($user_list, 'The user list should be an array');
			$this->assertTrue(count($user_list) > 1, 'The user list should contain more than one entry');
			foreach ($user_list as $usr) {
				$this->assertFalse($usr->user == self::UNKNOWN_USER, 'UNKNOWN_USER is known');
			}
		} finally {
			$this->done_ldap();
		}
	}
}
