<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\DTO;

use App\DTO\LdapConfiguration;
use App\Exceptions\LdapConfigurationException;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

/**
 * Unit tests for LdapConfiguration DTO.
 *
 * Tests configuration loading and validation logic.
 */
class LdapConfigurationTest extends AbstractTestCase
{
	public function testSuccessfulConfigurationLoading(): void
	{
		// Set up valid LDAP configuration
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
			'timeout' => 5,
			'use_tls' => true,
			'options' => [
				LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_DEMAND,
			],
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
			'admin_group_dn' => 'cn=admins,ou=groups,dc=example,dc=com',
			'auto_provision' => true,
		]);

		// Create configuration
		$config = new LdapConfiguration();

		// Verify all fields loaded correctly
		$this->assertSame('ldap.example.com', $config->host);
		$this->assertSame(389, $config->port);
		$this->assertSame('dc=example,dc=com', $config->base_dn);
		$this->assertSame('cn=admin,dc=example,dc=com', $config->bind_dn);
		$this->assertSame('adminpass', $config->bind_password);
		$this->assertSame('(uid=%s)', $config->user_filter);
		$this->assertSame('uid', $config->attr_username);
		$this->assertSame('mail', $config->attr_email);
		$this->assertSame('cn', $config->attr_display_name);
		$this->assertSame('cn=admins,ou=groups,dc=example,dc=com', $config->admin_group_dn);
		$this->assertTrue($config->auto_provision);
		$this->assertTrue($config->use_tls);
		$this->assertTrue($config->tls_verify_peer);
		$this->assertSame(5, $config->connection_timeout);
	}

	public function testTlsVerificationDisabled(): void
	{
		// Set up LDAP configuration with TLS verification disabled
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
			'timeout' => 5,
			'use_tls' => true,
			'options' => [
				LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_ALLOW,
			],
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$config = new LdapConfiguration();

		// Verify TLS verification is disabled
		$this->assertFalse($config->tls_verify_peer);
	}

	public function testDefaultValuesForOptionalSettings(): void
	{
		// Set up minimal LDAP configuration (missing optional fields)
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$config = new LdapConfiguration();

		// Verify defaults are set
		$this->assertNull($config->admin_group_dn);
		$this->assertTrue($config->auto_provision);
		$this->assertSame(5, $config->connection_timeout);
		$this->assertTrue($config->use_tls);
	}

	public function testMissingHostThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => [],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_HOST');

		new LdapConfiguration();
	}

	public function testMissingPortThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_PORT');

		new LdapConfiguration();
	}

	public function testMissingBaseDnThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BASE_DN');

		new LdapConfiguration();
	}

	public function testMissingBindDnThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BIND_DN');

		new LdapConfiguration();
	}

	public function testMissingBindPasswordThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_BIND_PASSWORD');

		new LdapConfiguration();
	}

	public function testMissingUserFilterThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_USER_FILTER');

		new LdapConfiguration();
	}

	public function testMissingUsernameAttributeThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_USERNAME');

		new LdapConfiguration();
	}

	public function testMissingEmailAttributeThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_EMAIL');

		new LdapConfiguration();
	}

	public function testMissingDisplayNameAttributeThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_ATTR_DISPLAY_NAME');

		new LdapConfiguration();
	}

	public function testEmptyStringValuesThrowException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => [''],
			'port' => 389,
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_HOST');

		new LdapConfiguration();
	}

	public function testInvalidPortThrowsException(): void
	{
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.example.com'],
			'port' => 'not-a-number',
			'base_dn' => 'dc=example,dc=com',
			'username' => 'cn=admin,dc=example,dc=com',
			'password' => 'adminpass',
		]);

		Config::set('ldap.auth', [
			'user_filter' => '(uid=%s)',
			'attributes' => [
				'username' => 'uid',
				'email' => 'mail',
				'display_name' => 'cn',
			],
		]);

		$this->expectException(LdapConfigurationException::class);
		$this->expectExceptionMessage('LDAP_PORT');

		new LdapConfiguration();
	}
}
