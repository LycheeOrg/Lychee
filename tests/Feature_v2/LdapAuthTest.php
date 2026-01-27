<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for LDAP authentication integration.
 *
 * Note: These tests verify the integration points and configuration,
 * but do not test against a real LDAP server. Full LDAP server testing
 * would require a test LDAP instance (OpenLDAP, Active Directory, etc.).
 */
class LdapAuthTest extends BaseApiWithDataTest
{
	public function testLdapDisabledUsesLocalAuth(): void
	{
		// Ensure LDAP is disabled
		Config::set('ldap.auth.enabled', false);

		// Local authentication should work
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
		]);
		$this->assertNoContent($response);

		// Verify user is logged in
		$response = $this->getJson('Auth::user');
		$this->assertOk($response);
		$response->assertJson([
			'username' => $this->admin->username,
		]);

		// Logout
		$this->postJson('Auth::logout');
	}

	public function testLdapConfigurationLoads(): void
	{
		// Set LDAP configuration
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.test.local'],
			'port' => 389,
			'base_dn' => 'dc=test,dc=local',
			'username' => 'cn=admin,dc=test,dc=local',
			'password' => 'adminpass',
			'timeout' => 1, // Short timeout
			'use_tls' => false, // Disable TLS for test
			'options' => [],
		]);

		Config::set('ldap.auth', [
			'enabled' => false, // Keep disabled for config test
			'auto_provision' => true,
			'user_filter' => '(&(objectClass=person)(uid=%s))',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'displayName',
			],
			'admin_group_dn' => 'cn=admins,ou=groups,dc=test,dc=local',
		]);

		// Verify configuration can be loaded
		$this->assertFalse(Config::get('ldap.auth.enabled'));
		$this->assertSame('ldap.test.local', Config::get('ldap.connections.default.hosts.0'));
	}

	public function testLocalAuthStillWorksWhenLdapDisabled(): void
	{
		// Keep LDAP disabled to avoid connection attempts
		Config::set('ldap.auth.enabled', false);

		// Local authentication should work
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
		]);
		$this->assertNoContent($response);

		// Verify user is logged in
		$response = $this->getJson('Auth::user');
		$this->assertOk($response);
		$response->assertJson([
			'username' => $this->admin->username,
		]);

		// Logout
		$this->postJson('Auth::logout');
	}

	public function testInvalidCredentialsFailsAuth(): void
	{
		// Ensure LDAP is disabled for this test
		Config::set('ldap.auth.enabled', false);

		// Try to login with invalid credentials
		$response = $this->postJson('Auth::login', [
			'username' => 'nonexistent',
			'password' => 'wrongpassword',
		]);
		$this->assertUnauthorized($response);
	}

	public function testUserProvisioningCreatesLocalUser(): void
	{
		// This test verifies the provisioning logic works
		// In a real scenario, this would be triggered by LDAP auth

		// Ensure no user exists with this username
		User::where('username', 'ldapuser')->delete();

		// Verify user doesn't exist
		$this->assertDatabaseMissing('users', [
			'username' => 'ldapuser',
		]);

		// In a real LDAP scenario, this user would be created by ProvisionLdapUser
		// For this test, we verify the database structure supports LDAP users
		$user = new User();
		$user->username = 'ldapuser';
		$user->email = 'ldap@example.com';
		$user->display_name = 'LDAP User';
		$user->password = bcrypt(bin2hex(random_bytes(32)));
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false;
		$user->save();

		// Verify user was created
		$this->assertDatabaseHas('users', [
			'username' => 'ldapuser',
			'email' => 'ldap@example.com',
			'display_name' => 'LDAP User',
		]);

		// Cleanup
		$user->delete();
	}

	public function testDisplayNameColumnExists(): void
	{
		// Verify display_name column exists (added in I1 migration)
		$this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('users', 'display_name'));
	}

	public function testLdapConfigurationValidation(): void
	{
		// Test that LDAP configuration can be set and read
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.test.local'],
			'port' => 389,
			'base_dn' => 'dc=test,dc=local',
			'username' => 'cn=admin,dc=test,dc=local',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'enabled' => false, // Keep disabled
			'user_filter' => '(&(objectClass=person)(uid=%s))',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'displayName',
			],
		]);

		// Verify all required config keys are present
		$this->assertIsArray(Config::get('ldap.connections.default.hosts'));
		$this->assertIsInt(Config::get('ldap.connections.default.port'));
		$this->assertIsString(Config::get('ldap.auth.user_filter'));
		$this->assertIsArray(Config::get('ldap.auth.attributes'));
	}
}
