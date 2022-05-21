<?php

namespace Tests\Feature;

use App\LDAP\LDAPFunctions;
use App\Models\Configs;
use Tests\Feature\Lib\LDAPTestFunctions;
use Tests\LDAPTestCase;

class LDAPTest extends LDAPTestCase
{
	protected $test_user = [
		'user' => self::TESTUSER, 'server' => 'ldap.forumsys.com', 'dn' => self::TESTUSER_DN,
		'display_name' => self::TESTUSER_CN, 'email' => self::TESTUSER_EMAIL,
	];

	public function testLDAPInternFunctions()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			$this->LDAP_open();
			$ldap->LDAP_get_option(LDAP_OPT_PROTOCOL_VERSION, $option);
			$this->assertEquals(3, $option, 'LDAP Version should be 3');
			$ldap->LDAP_get_option(LDAP_OPT_SIZELIMIT, $size_limit);
			$ldap->LDAP_set_option(LDAP_OPT_SIZELIMIT, '1000');
			$ldap->LDAP_get_option(LDAP_OPT_SIZELIMIT, $new_size);
			$this->assertEquals(1000, $new_size, 'Option cannot be reset');
			$ldap->LDAP_set_option(LDAP_OPT_SIZELIMIT, $size_limit);
			$ldap->LDAP_get_option(LDAP_OPT_SIZELIMIT, $new_sl);
			$this->assertEquals(0, $new_sl, 'Option cannot be reset');
			$Filter = LDAPTestFunctions::LDAP_makeFilter('%{uid} = %{start} test%{inc} %{end}',
										['uid' => 'gauss', 'start' => '{', 'end' => '}', 'inc' => '++', 'dec' => '--']);
			$this->assertEquals('gauss = { test++ }', $Filter, 'LDAP_makeFilter could not make the filter');

			$Filter = LDAPTestFunctions::LDAP_makeFilter('%{uid} = %{start} test%{inc} %{end}',
										['uid' => ['gauss'], 'start' => ['{'], 'end' => ['}'], 'inc' => ['++'], 'dec' => ['--']]);
			$this->assertEquals('gauss = { test++ }', $Filter, 'LDAP_makeFilter could not make the filter');

			$Filter = LDAPTestFunctions::LDAP_filterEscape("/\x03 \x05 \x08 (test) \\/");
			$this->assertEquals('/\03 \05 \08 \28test\29 \5c/', $Filter, 'LDAP_filterEscape could not escape properly');

			$ldap->LDAP_close();
			$did_except = false;
			try {
				$ldap->LDAP_set_option(-1, '1000');
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from LDAP_set_option');

			$this->LDAP_open();
			$did_except = false;
			try {
				$ldap->LDAP_get_option(-1);
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from LDAP_set_option');

			$this->LDAP_open();
			$did_except = false;
			try {
				$ldap->LDAP_start_tls();
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from LDAP_start_tls');
			$ldap->LDAP_close();
			$this->assertFalse(!$ldap->connect('ldaps://db.debian.org'), 'Cannot connect to ldaps protocol');
			$this->assertFalse(!$ldap->connect('ldap://db.debian.org'), 'Cannot connect to ldap protocol');
			$this->assertFalse(!$ldap->connect('ldap://db.debian.org:389'), 'Cannot connect to ldap protocol');
			$this->assertFalse($ldap->connect('db.debian.org', 8000), 'Cannot connect to ldap protocol');
			$this->assertFalse(!$ldap->connect('ldap.forumsys.com', 389, 0), 'Cannot connect to ldap protocol');
			$this->assertFalse($ldap->connect('ss:', 389, 0), 'It should not be possible to connect to ss:');
		} finally {
			$this->done_ldap();
		}
	}

	// Test LDAP bound level handling
	public function testLDAPBoundLevel()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			$this->LDAP_open();
			// The bound level after open_LDAP() is 2 if open_LDAP() does already the binding with the LDAP server.
			$this->assertEquals(2, $ldap->LDAP_get_bound(), 'Bound level should be SUPERUSER');

			// Call get_user_data() befor LDAP_bind() to check if the automatic binding is working
			// This also works with an anonymous binding LDAP_Server, verifyable by using a local server
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertEqualsCanonicalizing($user_data->toArray(), $this->test_user);
			$this->assertEquals(2, $ldap->LDAP_get_bound(), 'Bound level should be SUPERUSER');
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertEqualsCanonicalizing($user_data->toArray(), $this->test_user);

			$this->assertTrue($ldap->LDAP_bind(), 'ldap_bind has failed');
			$this->assertEquals(2, $ldap->LDAP_get_bound(), 'Bound level should be SUPERUSER');

			$this->assertTrue($ldap->LDAP_bind(self::TESTUSER_DN, self::TESTUSER_PW), 'ldap_bind has failed for TESTUSER_DN:TESTUSER_PW');
			$this->assertEquals(1, $ldap->LDAP_get_bound(), 'Bound level should be USER');
			$did_except = false;
			try {
				$ldap->LDAP_bind(self::UNKNOWN_USER, 'password');
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from ldap_bind when called with UNKNOWN_USER:password');
			$did_except = false;
			try {
				$ldap->LDAP_bind(self::TESTUSER_DN, 'WRONGPASS');
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from ldap_bind when called with TESTUSER_DN:WRONGPAS');
			$this->assertEquals(-1, $ldap->LDAP_get_bound(), 'Bound level should be UNBOUND');
			$ldap->clear_cache();
			// Test for Exception
			Configs::set('ldap_user_filter', '');
			$did_except = false;
			try {
				$user_data = $ldap->get_user_data(self::TESTUSER);
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from get_user_data because of multiple objects');
			// Test for Exception
			Configs::set('ldap_user_filter', '');
			$did_except = false;
			try {
				$ldap->check_pass(self::TESTUSER, self::TESTUSER_PW);
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from get_user_data because of multiple objects');

			$ldap->LDAP_close();
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPSearch()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			$this->LDAP_open();

			$SR = $ldap->LDAP_search(self::USER_TREE, self::TESTUSER_FILTER, 'sub');

			$this->assertFalse($SR['count'] == 0, 'LDAP_search got no result');
			$this->assertFalse($SR['count'] > 1, 'LDAP_search got more than one result, should be one');
			$SR = $ldap->LDAP_search(self::USER_TREE, '(objectClass=*)', 'base');
			$this->assertTrue($SR['count'] > 0, 'LDAP_search scope base should return at least one result');

			$SR = $ldap->LDAP_search(self::USER_TREE, '(objectClass=*)', 'one');
			$this->assertTrue($SR['count'] > 0, 'LDAP_search scope base should return at least one result');
			// Test for Exception
			$did_except = false;
			try {
				$SR = $ldap->LDAP_search(self::USER_TREE, '_', 'sub');
			} catch (\App\Exceptions\LDAPException $e) {
				$did_except = true;
			}
			$this->assertTrue($did_except, 'Missing Exception from get_user_data because of multiple objects');
			$ldap->LDAP_close();
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPmultiServer1()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			Configs::set('ldap_server', 'ss:,google.com,github.com');
			$this->expectException(\App\Exceptions\LDAPException::class, 'Missing Exception from check_pass when called with no server available');
			$this->LDAP_open();
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPmultiServer2()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			// We do not expect any Exception if a valid server is present
			Configs::set('ldap_server', 'google.com,github.com,' . self::SERVER);
			$this->LDAP_open();
			$this->assertTrue(true);
			$ldap->LDAP_close();
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPCheck()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			/*
			 * These tests are made without enabling LDAP as the authenication service (ldap_enabled is untouched)
			 *
			 * Scenario is as follows:
			 *
			 * 1. access get_user_data() before check_pass()
			 * 2. try to verify user: gauss password: password
			 * 3. try to verify user: euler password: password
			 * 4. try to verify user: UNKNOWN password: password ==> should fail
			 * 5. try to verify user: gauss password: test ==> should fail
			 * 6. call LDAPFunctions::get_user_data(TESTUSER) ==> should return an array with the data for TESTUSER
			 * 7. Check exception in case the server is not available
			 */

			// 1
			// accessing get_user_data() before check_pass()
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertEqualsCanonicalizing($user_data->toArray(), $this->test_user);
			if ($user_data) {
				$this->assertTrue($ldap->LDAP_bind($user_data->dn, self::TESTUSER_PW), 'Cannot ldap_bind to user TESTUSER');
			}
			// 2
			$this->assertTrue($ldap->check_pass(self::TESTUSER, self::TESTUSER_PW), 'Cannot verify user TESTUSER');
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertTrue(is_a($user_data, 'App\LDAP\LDAPUserData'), 'TESTUSER is unknown');

			// 3
			$user_data = $ldap->get_user_data(self::TESTUSER2);
			$this->assertTrue(is_a($user_data, 'App\LDAP\LDAPUserData'), 'TESTUSER2 is unknown');
			$this->assertTrue($ldap->check_pass(self::TESTUSER2, self::TESTUSER2_PW), 'Cannot verify user TESTUSER2');

			// 4
			$this->assertFalse($ldap->check_pass(self::UNKNOWN_USER, 'password'), 'Should not possible to verify the UNKNOWN_USER:TESTUSER_PW');

			$this->assertFalse($ldap->check_pass('test', '08154711'), 'Should not be possible to verify user test:08154711');
			// 5
			$this->assertFalse($ldap->check_pass(self::TESTUSER, '08154711'), 'Should not possible to verify user TESTUSER:08154711');

			// 6
			$user_data = $ldap->get_user_data(self::TESTUSER);
			$this->assertTrue(is_a($user_data, 'App\LDAP\LDAPUserData'), 'TESTUSER is unknown');
			$this->assertEqualsCanonicalizing($user_data->toArray(), $this->test_user);
			// 7
			$ldap->LDAP_close();
			Configs::set('ldap_user_tree', 'uid=%{user},dc=example,dc=com');
			$this->assertTrue($ldap->check_pass(self::TESTUSER, self::TESTUSER_PW), 'Cannot verify user TESTUSER');
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPinvalidServer1()
	{
		$this->assertTrue(true);
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			$ldap->LDAP_close();
			Configs::set('ldap_server', 'google.com,github.com');
			$this->expectException(\App\Exceptions\LDAPException::class, 'Missing Exception from check_pass when called with no server available');
			$ldap->check_pass(self::UNKNOWN_USER, 'password');
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPinvalidServer2()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			$ldap->LDAP_close();
			Configs::set('ldap_server', 'google.com,github.com,' . self::SERVER);
			$this->assertFalse($ldap->check_pass(self::UNKNOWN_USER, 'password'), 'Should not possible to verify the UNKNOWN_USER:TESTUSER_PW');
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPCheckUnknown()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
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

	public function testLDAPGetUserList()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			$this->LDAP_open();
			Configs::set('ldap_user_filter', '');
			$user_list = $ldap->get_user_list(true);
			$this->assertIsArray($user_list, 'The user list should be an array');
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPs()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			// Testing ldaps by using the public debian server
			// We try to get the list of users which uid starts with the letter a
			// There should be more than 100 of these users, So one is enough to prove
			// that the communications is working.
			Configs::set('ldap_server', 'ldaps://db.debian.org');
			Configs::set('ldap_port', '636');
			Configs::set('ldap_user_tree', 'dc=debian,dc=org');
			Configs::set('ldap_user_filter', 'uid=%{user}');
			Configs::set('ldap_bind_dn', '');
			Configs::set('ldap_bind_pw', '');
			$this->LDAP_open();
			$SR = $ldap->LDAP_search('dc=debian,dc=org', 'uid=a*', 'sub');
			$this->assertTrue($SR['count'] > 0, 'LDAP_search should return at least one result');
			$ldap->LDAP_close();
		} finally {
			$this->done_ldap();
		}
	}

	public function testLDAPstarttls()
	{
		$ldap = $this->get_ldap();
		if (!$ldap) {
			return;
		}
		try {
			// Testing ldap with starttls by using the public google server.
			Configs::set('ldap_server', 'db.debian.org');
			Configs::set('ldap_port', '389');
			Configs::set('ldap_start_tls', '1');
			Configs::set('ldap_user_tree', 'dc=debian,dc=org');
			Configs::set('ldap_user_filter', 'uid=%{user}');
			Configs::set('ldap_bind_dn', '');
			Configs::set('ldap_bind_pw', '');
			$this->LDAP_open();
			$SR = $ldap->LDAP_search('dc=debian,dc=org', 'uid=a*', 'sub');
			$this->assertTrue($SR['count'] > 0, 'LDAP_search should return at least one result');
			$ldap->LDAP_close();
		} finally {
			$this->done_ldap();
		}
	}
}
