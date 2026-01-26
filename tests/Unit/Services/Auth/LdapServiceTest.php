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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use LdapRecord\Auth\Guard;
use LdapRecord\Connection;
use LdapRecord\Query\Builder;
use Tests\AbstractTestCase;

/**
 * Unit tests for LdapService.
 *
 * Tests LDAP authentication, group queries, and connection handling.
 */
class LdapServiceTest extends AbstractTestCase
{
	private function getMockConfig(): LdapConfiguration
	{
		// Set up valid LDAP configuration for tests
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

		return new LdapConfiguration();
	}

	public function testSuccessfulAuthentication(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('notice')->never();
		Log::shouldReceive('warning')->never();
		Log::shouldReceive('error')->never();

		$config = $this->getMockConfig();

		// Mock LDAP connection
		$connection = $this->createMock(Connection::class);

		// Mock query builder for user search
		$searchQueryBuilder = $this->createMock(Builder::class);

		// Mock query builder for attribute retrieval
		$attrQueryBuilder = $this->createMock(Builder::class);

		// Setup connection to return different query builders
		$connection->expects($this->exactly(2))
			->method('query')
			->willReturnOnConsecutiveCalls($searchQueryBuilder, $attrQueryBuilder);

		// Setup search query builder
		$searchQueryBuilder->expects($this->once())
			->method('setDn')
			->with('dc=example,dc=com')
			->willReturnSelf();

		$searchQueryBuilder->expects($this->once())
			->method('rawFilter')
			->with('(uid=testuser)')
			->willReturnSelf();

		$searchQueryBuilder->expects($this->once())
			->method('limit')
			->with(1)
			->willReturnSelf();

		// Mock search results
		$searchResults = [
			['dn' => 'uid=testuser,ou=users,dc=example,dc=com'],
		];
		$searchQueryBuilder->expects($this->once())
			->method('get')
			->willReturn($searchResults);

		// Mock auth guard for bind attempt
		$authGuard = $this->createMock(Guard::class);
		$connection->expects($this->once())
			->method('auth')
			->willReturn($authGuard);

		$authGuard->expects($this->once())
			->method('attempt')
			->with('uid=testuser,ou=users,dc=example,dc=com', 'password123')
			->willReturn(true);

		// Setup attribute query builder
		$attrQueryBuilder->expects($this->once())
			->method('setDn')
			->with('uid=testuser,ou=users,dc=example,dc=com')
			->willReturnSelf();

		$attrQueryBuilder->expects($this->once())
			->method('read')
			->willReturnSelf();

		// Mock attribute results
		$attrResult = [
			'dn' => 'uid=testuser,ou=users,dc=example,dc=com',
			'mail' => ['test@example.com'],
			'cn' => ['Test User'],
		];
		$attrQueryBuilder->expects($this->once())
			->method('first')
			->willReturn($attrResult);

		// Create service with mocked connection
		$service = new LdapService($config, $connection);

		// Authenticate
		$ldapUser = $service->authenticate('testuser', 'password123');

		// Verify result
		$this->assertNotNull($ldapUser);
		$this->assertSame('testuser', $ldapUser->username);
		$this->assertSame('uid=testuser,ou=users,dc=example,dc=com', $ldapUser->userDn);
		$this->assertSame('test@example.com', $ldapUser->email);
		$this->assertSame('Test User', $ldapUser->display_name);
	}

	public function testAuthenticationFailsWhenUserNotFound(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('notice')->once()->with('LDAP user not found', ['username' => 'nonexistent']);
		Log::shouldReceive('warning')->never();
		Log::shouldReceive('error')->never();

		$config = $this->getMockConfig();

		// Mock LDAP connection
		$connection = $this->createMock(Connection::class);

		// Mock query builder for user search (returns no results)
		$queryBuilder = $this->createMock(Builder::class);
		$connection->expects($this->once())
			->method('query')
			->willReturn($queryBuilder);

		$queryBuilder->expects($this->once())
			->method('setDn')
			->with('dc=example,dc=com')
			->willReturnSelf();

		$queryBuilder->expects($this->once())
			->method('rawFilter')
			->with('(uid=nonexistent)')
			->willReturnSelf();

		$queryBuilder->expects($this->once())
			->method('limit')
			->with(1)
			->willReturnSelf();

		$queryBuilder->expects($this->once())
			->method('get')
			->willReturn([]);

		// Create service with mocked connection
		$service = new LdapService($config, $connection);

		// Authenticate
		$ldapUser = $service->authenticate('nonexistent', 'password123');

		// Verify result
		$this->assertNull($ldapUser);
	}

	public function testAuthenticationFailsWhenBindFails(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('notice')->once()->with('LDAP bind failed - invalid credentials', ['username' => 'testuser']);
		Log::shouldReceive('warning')->never();
		Log::shouldReceive('error')->never();

		$config = $this->getMockConfig();

		// Mock LDAP connection
		$connection = $this->createMock(Connection::class);

		// Mock query builder for user search
		$queryBuilder = $this->createMock(Builder::class);
		$connection->expects($this->once())
			->method('query')
			->willReturn($queryBuilder);

		$queryBuilder->expects($this->once())
			->method('setDn')
			->with('dc=example,dc=com')
			->willReturnSelf();

		$queryBuilder->expects($this->once())
			->method('rawFilter')
			->with('(uid=testuser)')
			->willReturnSelf();

		$queryBuilder->expects($this->once())
			->method('limit')
			->with(1)
			->willReturnSelf();

		// Mock search results
		$searchResults = [
			['dn' => 'uid=testuser,ou=users,dc=example,dc=com'],
		];
		$queryBuilder->expects($this->once())
			->method('get')
			->willReturn($searchResults);

		// Mock auth guard for bind attempt (fails)
		$authGuard = $this->createMock(Guard::class);
		$connection->expects($this->once())
			->method('auth')
			->willReturn($authGuard);

		$authGuard->expects($this->once())
			->method('attempt')
			->with('uid=testuser,ou=users,dc=example,dc=com', 'wrongpassword')
			->willReturn(false);

		// Create service with mocked connection
		$service = new LdapService($config, $connection);

		// Authenticate
		$ldapUser = $service->authenticate('testuser', 'wrongpassword');

		// Verify result
		$this->assertNull($ldapUser);
	}

	public function testAuthenticationThrowsOnConnectionError(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('error')->once();
		Log::shouldReceive('warning')->never();

		$config = $this->getMockConfig();

		// Mock LDAP connection that throws on query
		$connection = $this->createMock(Connection::class);
		$connection->expects($this->once())
			->method('query')
			->willThrowException(new LdapConnectionException('Connection timeout'));

		// Create service with mocked connection
		$service = new LdapService($config, $connection);

		// Expect authentication to rethrow LdapConnectionException
		$this->expectException(LdapConnectionException::class);
		$service->authenticate('testuser', 'password123');
	}

	public function testQueryGroups(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();

		$config = $this->getMockConfig();

		// Mock LDAP connection
		$connection = $this->createMock(Connection::class);

		// Mock query builder for group search
		$queryBuilder = $this->createMock(Builder::class);
		$connection->expects($this->once())
			->method('query')
			->willReturn($queryBuilder);

		$queryBuilder->expects($this->once())
			->method('setDn')
			->with('dc=example,dc=com')
			->willReturnSelf();

		$queryBuilder->expects($this->once())
			->method('rawFilter')
			->with('(member=uid=testuser,ou=users,dc=example,dc=com)')
			->willReturnSelf();

		// Mock group results
		$groupResults = [
			['dn' => 'cn=admins,ou=groups,dc=example,dc=com'],
			['dn' => 'cn=users,ou=groups,dc=example,dc=com'],
		];
		$queryBuilder->expects($this->once())
			->method('get')
			->willReturn($groupResults);

		// Create service with mocked connection
		$service = new LdapService($config, $connection);

		// Query groups
		$groups = $service->queryGroups('uid=testuser,ou=users,dc=example,dc=com');

		// Verify result
		$this->assertCount(2, $groups);
		$this->assertSame('cn=admins,ou=groups,dc=example,dc=com', $groups[0]);
		$this->assertSame('cn=users,ou=groups,dc=example,dc=com', $groups[1]);
	}

	public function testQueryGroupsReturnsEmptyArrayOnError(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('warning')->once();

		$config = $this->getMockConfig();

		// Mock LDAP connection that throws on query
		$connection = $this->createMock(Connection::class);
		$connection->expects($this->once())
			->method('query')
			->willThrowException(new \Exception('Query failed'));

		// Create service with mocked connection
		$service = new LdapService($config, $connection);

		// Query groups
		$groups = $service->queryGroups('uid=testuser,ou=users,dc=example,dc=com');

		// Verify result (empty array on error)
		$this->assertSame([], $groups);
	}

	public function testIsUserInAdminGroupWhenUserIsAdmin(): void
	{
		Log::shouldReceive('debug')->never();
		Log::shouldReceive('info')->once();

		$config = $this->getMockConfig();
		$service = new LdapService($config);

		// User is in admin group
		$groupDns = [
			'cn=admins,ou=groups,dc=example,dc=com',
			'cn=users,ou=groups,dc=example,dc=com',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin);
	}

	public function testIsUserInAdminGroupWhenUserIsNotAdmin(): void
	{
		Log::shouldReceive('debug')->once();
		Log::shouldReceive('info')->never();

		$config = $this->getMockConfig();
		$service = new LdapService($config);

		// User is not in admin group
		$groupDns = [
			'cn=users,ou=groups,dc=example,dc=com',
			'cn=developers,ou=groups,dc=example,dc=com',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertFalse($isAdmin);
	}

	public function testIsUserInAdminGroupWhenNoAdminGroupConfigured(): void
	{
		Log::shouldReceive('debug')->once();

		// Set up config without admin group
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
		$service = new LdapService($config);

		// Even if user is in admin group, should return false (no admin group configured)
		$groupDns = [
			'cn=admins,ou=groups,dc=example,dc=com',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertFalse($isAdmin);
	}

	public function testIsUserInAdminGroupCaseInsensitive(): void
	{
		Log::shouldReceive('info')->once();

		$config = $this->getMockConfig();
		$service = new LdapService($config);

		// User is in admin group (different case)
		$groupDns = [
			'CN=Admins,OU=Groups,DC=Example,DC=Com',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertTrue($isAdmin);
	}

	public function testAuthenticationWithMissingEmailAttribute(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('warning')->once();

		$config = $this->getMockConfig();

		// Mock LDAP connection
		$connection = $this->createMock(Connection::class);

		// Mock query builders
		$searchQueryBuilder = $this->createMock(Builder::class);
		$attrQueryBuilder = $this->createMock(Builder::class);

		$connection->expects($this->exactly(2))
			->method('query')
			->willReturnOnConsecutiveCalls($searchQueryBuilder, $attrQueryBuilder);

		// Setup search query
		$searchQueryBuilder->expects($this->once())
			->method('setDn')
			->with('dc=example,dc=com')
			->willReturnSelf();

		$searchQueryBuilder->expects($this->once())
			->method('rawFilter')
			->with('(uid=testuser)')
			->willReturnSelf();

		$searchQueryBuilder->expects($this->once())
			->method('limit')
			->with(1)
			->willReturnSelf();

		$searchResults = [
			['dn' => 'uid=testuser,ou=users,dc=example,dc=com'],
		];
		$searchQueryBuilder->expects($this->once())
			->method('get')
			->willReturn($searchResults);

		// Mock auth guard
		$authGuard = $this->createMock(Guard::class);
		$connection->expects($this->once())
			->method('auth')
			->willReturn($authGuard);

		$authGuard->expects($this->once())
			->method('attempt')
			->willReturn(true);

		// Setup attribute query (missing email)
		$attrQueryBuilder->expects($this->once())
			->method('setDn')
			->with('uid=testuser,ou=users,dc=example,dc=com')
			->willReturnSelf();

		$attrQueryBuilder->expects($this->once())
			->method('read')
			->willReturnSelf();

		// Mock attribute results (missing mail attribute)
		$attrResult = [
			'dn' => 'uid=testuser,ou=users,dc=example,dc=com',
			'cn' => ['Test User'],
		];
		$attrQueryBuilder->expects($this->once())
			->method('first')
			->willReturn($attrResult);

		$service = new LdapService($config, $connection);

		// Missing email attribute causes authentication to return null (caught exception)
		$ldapUser = $service->authenticate('testuser', 'password123');

		$this->assertNull($ldapUser);
	}

	public function testAuthenticationWithMissingDisplayNameAttribute(): void
	{
		Log::shouldReceive('debug')->zeroOrMoreTimes();
		Log::shouldReceive('warning')->once();

		$config = $this->getMockConfig();

		// Mock LDAP connection
		$connection = $this->createMock(Connection::class);

		// Mock query builders
		$searchQueryBuilder = $this->createMock(Builder::class);
		$attrQueryBuilder = $this->createMock(Builder::class);

		$connection->expects($this->exactly(2))
			->method('query')
			->willReturnOnConsecutiveCalls($searchQueryBuilder, $attrQueryBuilder);

		// Setup search query
		$searchQueryBuilder->expects($this->once())
			->method('setDn')
			->with('dc=example,dc=com')
			->willReturnSelf();

		$searchQueryBuilder->expects($this->once())
			->method('rawFilter')
			->with('(uid=testuser)')
			->willReturnSelf();

		$searchQueryBuilder->expects($this->once())
			->method('limit')
			->with(1)
			->willReturnSelf();

		$searchResults = [
			['dn' => 'uid=testuser,ou=users,dc=example,dc=com'],
		];
		$searchQueryBuilder->expects($this->once())
			->method('get')
			->willReturn($searchResults);

		// Mock auth guard
		$authGuard = $this->createMock(Guard::class);
		$connection->expects($this->once())
			->method('auth')
			->willReturn($authGuard);

		$authGuard->expects($this->once())
			->method('attempt')
			->willReturn(true);

		// Setup attribute query (missing display name)
		$attrQueryBuilder->expects($this->once())
			->method('setDn')
			->with('uid=testuser,ou=users,dc=example,dc=com')
			->willReturnSelf();

		$attrQueryBuilder->expects($this->once())
			->method('read')
			->willReturnSelf();

		// Mock attribute results (missing cn attribute)
		$attrResult = [
			'dn' => 'uid=testuser,ou=users,dc=example,dc=com',
			'mail' => ['test@example.com'],
		];
		$attrQueryBuilder->expects($this->once())
			->method('first')
			->willReturn($attrResult);

		$service = new LdapService($config, $connection);

		// Missing display name attribute causes authentication to return null (caught exception)
		$ldapUser = $service->authenticate('testuser', 'password123');

		$this->assertNull($ldapUser);
	}
}
