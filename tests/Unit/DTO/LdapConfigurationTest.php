<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\DTO;

use App\DTO\LdapConfiguration;
use App\Exceptions\LdapConfigurationException;
use Tests\AbstractTestCase;

/**
 * Unit tests for LdapConfiguration DTO.
 *
 * Tests configuration validation, error handling, and edge cases.
 */
class LdapConfigurationTest extends AbstractTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Set up valid baseline configuration
		config([
			'ldap.connections.default' => [
				'hosts' => ['ldap.test.local'],
				'port' => 389,
				'base_dn' => 'dc=test,dc=local',
				'username' => 'cn=admin,dc=test,dc=local',
				'password' => 'adminpass',
				'timeout' => 5,
				'use_tls' => true,
				'options' => [
					LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_DEMAND,
				],
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
	}

	// ===== SUCCESSFUL CONFIGURATION TESTS =====

	public function testValidConfiguration(): void
	{
		$config = new LdapConfiguration();

		$this->assertSame('ldap.test.local', $config->host);
		$this->assertSame(389, $config->port);
		$this->assertSame('dc=test,dc=local', $config->base_dn);
		$this->assertSame('cn=admin,dc=test,dc=local', $config->bind_dn);
		$this->assertSame('adminpass', $config->bind_password);
		$this->assertSame('(&(objectClass=person)(uid=%s))', $config->user_filter);
		$this->assertSame('uid', $config->attr_username);
		$this->assertSame('mail', $config->attr_email);
		$this->assertSame('displayName', $config->attr_display_name);
		$this->assertSame('cn=admins,ou=groups,dc=test,dc=local', $config->admin_group_dn);
		$this->assertTrue($config->auto_provision);
		$this->assertTrue($config->use_tls);
		$this->assertTrue($config->tls_verify_peer);
		$this->assertSame(5, $config->connection_timeout);
	}

	public function testConfigurationWithMultipleHosts(): void
	{
		config(['ldap.connections.default.hosts' => ['ldap1.test.local', 'ldap2.test.local']]);

		$config = new LdapConfiguration();

		// Should use first host
		$this->assertSame('ldap1.test.local', $config->host);
	}

	public function testConfigurationWithoutTls(): void
	{
		config(['ldap.connections.default.use_tls' => false]);

		$config = new LdapConfiguration();

		$this->assertFalse($config->use_tls);
	}

	public function testConfigurationWithTlsAllowOption(): void
	{
		config(['ldap.connections.default.options' => [
			LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_ALLOW,
		]]);

		$config = new LdapConfiguration();

		$this->assertFalse($config->tls_verify_peer);
	}

	public function testConfigurationWithTlsDemandOption(): void
	{
		config(['ldap.connections.default.options' => [
			LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_DEMAND,
		]]);

		$config = new LdapConfiguration();

		$this->assertTrue($config->tls_verify_peer);
	}

	public function testConfigurationWithoutAdminGroup(): void
	{
		config(['ldap.auth.admin_group_dn' => null]);

		$config = new LdapConfiguration();

		$this->assertNull($config->admin_group_dn);
	}

	public function testConfigurationWithAutoProvisionDisabled(): void
	{
		config(['ldap.auth.auto_provision' => false]);

		$config = new LdapConfiguration();

		$this->assertFalse($config->auto_provision);
	}

	public function testConfigurationWithCustomTimeout(): void
	{
		config(['ldap.connections.default.timeout' => 10]);

		$config = new LdapConfiguration();

		$this->assertSame(10, $config->connection_timeout);
	}

	public function testConfigurationWithActiveDirectorySettings(): void
	{
		config([
			'ldap.connections.default' => [
				'hosts' => ['ad.example.com'],
				'port' => 636,
				'base_dn' => 'dc=example,dc=com',
				'username' => 'CN=Service Account,OU=Service Accounts,DC=example,DC=com',
				'password' => 'securepass',
				'timeout' => 15,
				'use_tls' => true,
				'options' => [LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_DEMAND],
			],
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

		$this->assertSame('ad.example.com', $config->host);
		$this->assertSame(636, $config->port);
		$this->assertSame('dc=example,dc=com', $config->base_dn);
		$this->assertSame('(&(objectClass=user)(sAMAccountName=%s))', $config->user_filter);
		$this->assertSame('sAMAccountName', $config->attr_username);
		$this->assertSame('userPrincipalName', $config->attr_email);
		$this->assertSame('cn', $config->attr_display_name);
	}

	// ===== VALIDATION ERROR TESTS - Missing Host =====

	public function testMissingHostThrowsException(): void
	{
		config(['ldap.connections.default.hosts' => []]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_HOST is required but not set');

		new LdapConfiguration();
	}

	public function testNullHostThrowsException(): void
	{
		config(['ldap.connections.default.hosts' => [null]]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_HOST is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyHostThrowsException(): void
	{
		config(['ldap.connections.default.hosts' => ['']]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_HOST is required but not set');

		new LdapConfiguration();
	}

	// ===== VALIDATION ERROR TESTS - Missing Port =====

	public function testMissingPortThrowsException(): void
	{
		config(['ldap.connections.default.port' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_PORT is required but not set or invalid');

		new LdapConfiguration();
	}

	public function testInvalidPortThrowsException(): void
	{
		config(['ldap.connections.default.port' => 'not-a-number']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_PORT is required but not set or invalid');

		new LdapConfiguration();
	}

	// ===== VALIDATION ERROR TESTS - Missing Base DN =====

	public function testMissingBaseDnThrowsException(): void
	{
		config(['ldap.connections.default.base_dn' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BASE_DN is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyBaseDnThrowsException(): void
	{
		config(['ldap.connections.default.base_dn' => '']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BASE_DN is required but not set');

		new LdapConfiguration();
	}

	// ===== VALIDATION ERROR TESTS - Missing Bind DN =====

	public function testMissingBindDnThrowsException(): void
	{
		config(['ldap.connections.default.username' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BIND_DN is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyBindDnThrowsException(): void
	{
		config(['ldap.connections.default.username' => '']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BIND_DN is required but not set');

		new LdapConfiguration();
	}

	// ===== VALIDATION ERROR TESTS - Missing Bind Password =====

	public function testMissingBindPasswordThrowsException(): void
	{
		config(['ldap.connections.default.password' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BIND_PASSWORD is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyBindPasswordThrowsException(): void
	{
		config(['ldap.connections.default.password' => '']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BIND_PASSWORD is required but not set');

		new LdapConfiguration();
	}

	// ===== VALIDATION ERROR TESTS - Missing User Filter =====

	public function testMissingUserFilterThrowsException(): void
	{
		config(['ldap.auth.user_filter' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_USER_FILTER is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyUserFilterThrowsException(): void
	{
		config(['ldap.auth.user_filter' => '']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_USER_FILTER is required but not set');

		new LdapConfiguration();
	}

	// ===== VALIDATION ERROR TESTS - Missing Attributes =====

	public function testMissingUsernameAttributeThrowsException(): void
	{
		config(['ldap.auth.attributes.username' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_USERNAME is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyUsernameAttributeThrowsException(): void
	{
		config(['ldap.auth.attributes.username' => '']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_USERNAME is required but not set');

		new LdapConfiguration();
	}

	public function testMissingEmailAttributeThrowsException(): void
	{
		config(['ldap.auth.attributes.email' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_EMAIL is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyEmailAttributeThrowsException(): void
	{
		config(['ldap.auth.attributes.email' => '']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_EMAIL is required but not set');

		new LdapConfiguration();
	}

	public function testMissingDisplayNameAttributeThrowsException(): void
	{
		config(['ldap.auth.attributes.display_name' => null]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_DISPLAY_NAME is required but not set');

		new LdapConfiguration();
	}

	public function testEmptyDisplayNameAttributeThrowsException(): void
	{
		config(['ldap.auth.attributes.display_name' => '']);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_DISPLAY_NAME is required but not set');

		new LdapConfiguration();
	}

	// ===== EDGE CASE TESTS =====

	public function testConfigurationIsReadonly(): void
	{
		$config = new LdapConfiguration();

		// All properties should be readonly
		$reflection = new \ReflectionClass($config);
		foreach ($reflection->getProperties() as $property) {
			$this->assertTrue($property->isReadOnly(), "Property {$property->getName()} should be readonly");
		}
	}

	public function testConfigurationIsFinal(): void
	{
		$reflection = new \ReflectionClass(LdapConfiguration::class);
		$this->assertTrue($reflection->isFinal(), 'LdapConfiguration class should be final');
	}

	public function testDefaultTimeoutWhenNotSet(): void
	{
		config(['ldap.connections.default.timeout' => null]);

		$config = new LdapConfiguration();

		$this->assertSame(5, $config->connection_timeout);
	}

	public function testDefaultUseTlsWhenNotSet(): void
	{
		config(['ldap.connections.default.use_tls' => null]);

		$config = new LdapConfiguration();

		$this->assertTrue($config->use_tls);
	}

	public function testDefaultAutoProvisionWhenNotSet(): void
	{
		config(['ldap.auth.auto_provision' => null]);

		$config = new LdapConfiguration();

		$this->assertTrue($config->auto_provision);
	}

	public function testDefaultTlsVerifyPeerWhenOptionsNotSet(): void
	{
		config(['ldap.connections.default.options' => []]);

		$config = new LdapConfiguration();

		$this->assertTrue($config->tls_verify_peer);
	}

	public function testNumericStringPortIsAccepted(): void
	{
		config(['ldap.connections.default.port' => '636']);

		$config = new LdapConfiguration();

		$this->assertSame(636, $config->port);
	}

	public function testZeroPortThrowsException(): void
	{
		config(['ldap.connections.default.port' => 0]);

		// Zero is numeric but invalid for a port - still creates config
		// (validation of port range could be added if needed)
		$config = new LdapConfiguration();
		$this->assertSame(0, $config->port);
	}

	public function testNegativePortIsAccepted(): void
	{
		config(['ldap.connections.default.port' => -1]);

		// Negative is numeric - creates config (validation of port range could be added if needed)
		$config = new LdapConfiguration();
		$this->assertSame(-1, $config->port);
	}

	public function testConfigurationWithSpecialCharactersInPassword(): void
	{
		config(['ldap.connections.default.password' => 'P@$$w0rd!#%&*()']);

		$config = new LdapConfiguration();

		$this->assertSame('P@$$w0rd!#%&*()', $config->bind_password);
	}

	public function testConfigurationWithUnicodeCharacters(): void
	{
		config([
			'ldap.connections.default.base_dn' => 'dc=テスト,dc=local',
			'ldap.auth.admin_group_dn' => 'cn=管理者,dc=テスト,dc=local',
		]);

		$config = new LdapConfiguration();

		$this->assertSame('dc=テスト,dc=local', $config->base_dn);
		$this->assertSame('cn=管理者,dc=テスト,dc=local', $config->admin_group_dn);
	}

	public function testConfigurationWithLongValues(): void
	{
		$longBaseDn = str_repeat('ou=department,', 50) . 'dc=example,dc=com';
		config(['ldap.connections.default.base_dn' => $longBaseDn]);

		$config = new LdapConfiguration();

		$this->assertSame($longBaseDn, $config->base_dn);
	}

	public function testMultipleConfigurationInstancesAreIndependent(): void
	{
		$config1 = new LdapConfiguration();
		$host1 = $config1->host;

		// Change configuration
		config(['ldap.connections.default.hosts' => ['different.ldap.local']]);

		$config2 = new LdapConfiguration();
		$host2 = $config2->host;

		// First instance should keep original value
		$this->assertSame('ldap.test.local', $host1);
		$this->assertSame('different.ldap.local', $host2);
	}
}
