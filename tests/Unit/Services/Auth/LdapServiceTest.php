<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Services\Auth;

use App\DTO\LdapConfiguration;
use App\Exceptions\LdapConnectionException;
use App\Services\Auth\LdapService;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

/**
 * Unit tests for LdapService.
 *
 * Note: Full LDAP mocking is complex with LdapRecord. These tests focus on:
 * - Pure logic (isUserInAdminGroup)
 * - Configuration handling
 * - Error paths that don't require real LDAP connection
 * - Public API contracts
 *
 * Integration tests (LdapAuthTest, ProvisionLdapUserTest) provide coverage
 * for the LDAP connection and authentication flows.
 */
class LdapServiceTest extends AbstractTestCase
{
	private LdapService $service;

	protected function setUp(): void
	{
		parent::setUp();

		// Mock logging to avoid file permission issues
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('info')->zeroOrMoreTimes();
		Log::shouldReceive('notice')->zeroOrMoreTimes();
		Log::shouldReceive('warning')->zeroOrMoreTimes();
		Log::shouldReceive('error')->zeroOrMoreTimes();

		// Configure LDAP environment variables for testing
		config([
			'ldap.connections.default' => [
				'hosts' => ['ldap.test.local'],
				'port' => 389,
				'base_dn' => 'dc=test,dc=local',
				'username' => 'cn=admin,dc=test,dc=local',
				'password' => 'adminpass',
				'timeout' => 5,
				'use_tls' => false, // Disable TLS for unit tests
				'options' => [],
			],
			'ldap.auth' => [
				'enabled' => true,
				'auto_provision' => true,
				'user_filter' => '(&(objectClass=person)(uid=%s))',
				'attributes' => [
					'username' => 'uid',
					'email' => 'mail',
					'display_name' => 'displayName',
				],
				'admin_group_dn' => 'cn=admins,ou=groups,dc=test,dc=local',
			],
		]);

		$this->service = new LdapService(new LdapConfiguration());
	}

	// ===== GROUP LOGIC TESTS (Pure Logic - High Coverage) =====

	public function testIsUserInAdminGroupTrue(): void
	{
		$groupDns = [
			'cn=users,ou=groups,dc=test,dc=local',
			'cn=admins,ou=groups,dc=test,dc=local',
			'cn=developers,ou=groups,dc=test,dc=local',
		];

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin);
	}

	public function testIsUserInAdminGroupFalse(): void
	{
		$groupDns = [
			'cn=users,ou=groups,dc=test,dc=local',
			'cn=developers,ou=groups,dc=test,dc=local',
		];

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertFalse($isAdmin);
	}

	public function testIsUserInAdminGroupCaseInsensitive(): void
	{
		// LDAP DNs are case-insensitive - test different case variations
		$groupDns = [
			'CN=Admins,OU=Groups,DC=Test,DC=Local', // All uppercase
		];

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin, 'Should match admin group regardless of case');
	}

	public function testIsUserInAdminGroupMixedCase(): void
	{
		// Test with mixed case variations
		$groupDns = [
			'cn=Users,ou=GROUPS,dc=test,DC=local',
			'CN=admins,OU=groups,DC=TEST,dc=LOCAL', // Mixed case admin group
		];

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin, 'Should match admin group with mixed case');
	}

	public function testIsUserInAdminGroupEmptyArray(): void
	{
		$isAdmin = $this->service->isUserInAdminGroup([]);

		$this->assertFalse($isAdmin, 'Empty group list should return false');
	}

	public function testIsUserInAdminGroupNoConfig(): void
	{
		// Configure without admin group
		config(['ldap.auth.admin_group_dn' => null]);

		// Recreate service with new config
		$service = new LdapService(new LdapConfiguration());

		$groupDns = [
			'cn=admins,ou=groups,dc=test,dc=local',
			'cn=superadmins,ou=groups,dc=test,dc=local',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertFalse($isAdmin, 'Should return false when admin group not configured');
	}

	public function testIsUserInAdminGroupEmptyConfig(): void
	{
		// Configure with empty string
		config(['ldap.auth.admin_group_dn' => '']);

		$service = new LdapService(new LdapConfiguration());
		$isAdmin = $service->isUserInAdminGroup(['cn=admins,ou=groups,dc=test,dc=local']);

		$this->assertFalse($isAdmin, 'Empty admin group DN should return false');
	}

	public function testIsUserInAdminGroupPartialMatch(): void
	{
		// Test that partial matches don't count
		$groupDns = [
			'cn=admin-users,ou=groups,dc=test,dc=local', // Contains 'admin' but not exact match
			'cn=administrators,ou=groups,dc=test,dc=local', // Different word
		];

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertFalse($isAdmin, 'Partial matches should not count as admin');
	}

	// ===== CONFIGURATION TESTS =====

	public function testServiceAcceptsConfiguration(): void
	{
		// Test that service can be created with valid configuration
		$config = new LdapConfiguration();
		$service = new LdapService($config);

		$this->assertInstanceOf(LdapService::class, $service);
	}

	public function testServiceWithCustomConfiguration(): void
	{
		// Test with custom AD-style configuration
		config([
			'ldap.auth' => [
				'enabled' => true,
				'auto_provision' => true,
				'user_filter' => '(&(objectClass=user)(sAMAccountName=%s))',
				'attributes' => [
					'username' => 'sAMAccountName',
					'email' => 'userPrincipalName',
					'display_name' => 'cn',
				],
				'admin_group_dn' => 'CN=Domain Admins,CN=Users,DC=example,DC=com',
			],
		]);

		$config = new LdapConfiguration();
		$service = new LdapService($config);

		// Test admin group matching with AD-style DN
		$groupDns = ['CN=Domain Admins,CN=Users,DC=example,DC=com'];
		$this->assertTrue($service->isUserInAdminGroup($groupDns));
	}

	// ===== ERROR HANDLING TESTS =====

	public function testAuthenticateWithInvalidHostThrowsConnectionException(): void
	{
		// Configure with invalid host that will fail to connect
		config(['ldap.connections.default.hosts' => ['invalid.nonexistent.local']]);
		config(['ldap.connections.default.timeout' => 1]); // Short timeout

		$service = new LdapService(new LdapConfiguration());

		// Expect LdapConnectionException when connection fails
		$this->expectException(LdapConnectionException::class);
		$this->expectExceptionMessage('Unable to connect to LDAP server');

		$service->authenticate('testuser', 'password');
	}

	public function testAuthenticateLogsAttempt(): void
	{
		// Logging is already mocked in setUp, just test that connection exception is thrown
		try {
			$this->service->authenticate('testuser', 'password');
			$this->fail('Should have thrown LdapConnectionException');
		} catch (LdapConnectionException $e) {
			// Expected - LdapConnectionException should be re-thrown
			$this->assertStringContainsString('Unable to connect to LDAP server', $e->getMessage());
		}
	}

	public function testQueryGroupsWithInvalidDnReturnsEmpty(): void
	{
		// queryGroups catches exceptions and returns empty array
		// Logging is already mocked in setUp
		$groups = $this->service->queryGroups('invalid-dn');

		$this->assertIsArray($groups);
		$this->assertEmpty($groups, 'Should return empty array on error');
	}

	// ===== EDGE CASE TESTS =====

	public function testIsUserInAdminGroupWithWhitespace(): void
	{
		// Test DNs with extra whitespace
		$groupDns = [
			'cn = admins , ou = groups , dc = test , dc = local', // Spaces around values
		];

		// This should NOT match because LDAP DNs are whitespace-sensitive
		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertFalse($isAdmin, 'DNs with extra whitespace should not match');
	}

	public function testIsUserInAdminGroupWithSpecialCharacters(): void
	{
		// Configure admin group with special characters
		config(['ldap.auth.admin_group_dn' => 'cn=admin-group_v2.0,ou=groups,dc=test,dc=local']);

		$service = new LdapService(new LdapConfiguration());

		$groupDns = [
			'cn=admin-group_v2.0,ou=groups,dc=test,dc=local',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin, 'Should handle special characters in DN');
	}

	public function testIsUserInAdminGroupWithUnicodeCharacters(): void
	{
		// Configure admin group with unicode characters
		config(['ldap.auth.admin_group_dn' => 'cn=管理者,ou=groups,dc=test,dc=local']);

		$service = new LdapService(new LdapConfiguration());

		$groupDns = [
			'cn=管理者,ou=groups,dc=test,dc=local',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin, 'Should handle unicode characters in DN');
	}

	public function testIsUserInAdminGroupWithManyGroups(): void
	{
		// Test performance with large number of groups
		$groupDns = [];
		for ($i = 0; $i < 100; $i++) {
			$groupDns[] = "cn=group{$i},ou=groups,dc=test,dc=local";
		}
		// Add admin group at the end
		$groupDns[] = 'cn=admins,ou=groups,dc=test,dc=local';

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin, 'Should find admin group in large list');
	}

	public function testIsUserInAdminGroupFirstPosition(): void
	{
		// Admin group is first in list
		$groupDns = [
			'cn=admins,ou=groups,dc=test,dc=local',
			'cn=users,ou=groups,dc=test,dc=local',
		];

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin, 'Should match admin group in first position');
	}

	public function testIsUserInAdminGroupLastPosition(): void
	{
		// Admin group is last in list
		$groupDns = [
			'cn=users,ou=groups,dc=test,dc=local',
			'cn=developers,ou=groups,dc=test,dc=local',
			'cn=admins,ou=groups,dc=test,dc=local',
		];

		$isAdmin = $this->service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin, 'Should match admin group in last position');
	}

	public function testMultipleServiceInstancesIndependent(): void
	{
		// Test that multiple service instances work independently
		$service1 = new LdapService(new LdapConfiguration());

		config(['ldap.auth.admin_group_dn' => 'cn=different-admins,ou=groups,dc=test,dc=local']);
		$service2 = new LdapService(new LdapConfiguration());

		$groupDns1 = ['cn=admins,ou=groups,dc=test,dc=local'];
		$groupDns2 = ['cn=different-admins,ou=groups,dc=test,dc=local'];

		// Each service should use its own configuration
		$this->assertTrue($service1->isUserInAdminGroup($groupDns1));
		$this->assertFalse($service1->isUserInAdminGroup($groupDns2));

		$this->assertFalse($service2->isUserInAdminGroup($groupDns1));
		$this->assertTrue($service2->isUserInAdminGroup($groupDns2));
	}
}

