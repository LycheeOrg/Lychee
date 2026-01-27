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
			'hosts' => ['ldap.mycompany.local'],
			'port' => 389,
			'base_dn' => 'dc=mycompany,dc=local',
			'username' => 'cn=lychee-bind,ou=services,dc=mycompany,dc=local',
			'password' => 'secretpassword123',
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
			'admin_group_dn' => 'cn=admins,ou=groups,dc=mycompany,dc=local',
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
			->with('dc=mycompany,dc=local')
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
			['dn' => 'uid=testuser,ou=users,dc=mycompany,dc=local'],
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
			->with('uid=testuser,ou=users,dc=mycompany,dc=local', 'password123')
			->willReturn(true);

		// Setup attribute query builder
		$attrQueryBuilder->expects($this->once())
			->method('setDn')
			->with('uid=testuser,ou=users,dc=mycompany,dc=local')
			->willReturnSelf();

		$attrQueryBuilder->expects($this->once())
			->method('read')
			->willReturnSelf();

		// Mock attribute results
		$attrResult = [
			'dn' => 'uid=testuser,ou=users,dc=mycompany,dc=local',
			'mail' => ['test@mycompany.local'],
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
		$this->assertSame('uid=testuser,ou=users,dc=mycompany,dc=local', $ldapUser->user_dn);
		$this->assertSame('test@mycompany.local', $ldapUser->email);
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
			->with('dc=mycompany,dc=local')
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
			->with('dc=mycompany,dc=local')
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
			['dn' => 'uid=testuser,ou=users,dc=mycompany,dc=local'],
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
			->with('uid=testuser,ou=users,dc=mycompany,dc=local', 'wrongpassword')
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
			->with('dc=mycompany,dc=local')
			->willReturnSelf();

		$queryBuilder->expects($this->once())
			->method('rawFilter')
			->with('(member=uid=testuser,ou=users,dc=mycompany,dc=local)')
			->willReturnSelf();

		// Mock group results
		$groupResults = [
			['dn' => 'cn=admins,ou=groups,dc=mycompany,dc=local'],
			['dn' => 'cn=users,ou=groups,dc=mycompany,dc=local'],
		];
		$queryBuilder->expects($this->once())
			->method('get')
			->willReturn($groupResults);

		// Create service with mocked connection
		$service = new LdapService($config, $connection);

		// Query groups
		$groups = $service->queryGroups('uid=testuser,ou=users,dc=mycompany,dc=local');

		// Verify result
		$this->assertCount(2, $groups);
		$this->assertSame('cn=admins,ou=groups,dc=mycompany,dc=local', $groups[0]);
		$this->assertSame('cn=users,ou=groups,dc=mycompany,dc=local', $groups[1]);
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
		$groups = $service->queryGroups('uid=testuser,ou=users,dc=mycompany,dc=local');

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
			'cn=admins,ou=groups,dc=mycompany,dc=local',
			'cn=users,ou=groups,dc=mycompany,dc=local',
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
			'cn=users,ou=groups,dc=mycompany,dc=local',
			'cn=developers,ou=groups,dc=mycompany,dc=local',
		];

		$isAdmin = $service->isUserInAdminGroup($groupDns);

		$this->assertFalse($isAdmin);
	}

	public function testIsUserInAdminGroupWhenNoAdminGroupConfigured(): void
	{
		Log::shouldReceive('debug')->once();

		// Set up config without admin group
		Config::set('ldap.connections.default', [
			'hosts' => ['ldap.testorg.local'],
			'port' => 389,
			'base_dn' => 'dc=testorg,dc=local',
			'username' => 'cn=lychee-bind,ou=services,dc=testorg,dc=local',
			'password' => 'secretPassword789',
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
			'cn=admins,ou=groups,dc=mycompany,dc=local',
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
			'CN=Admins,OU=Groups,DC=Mycompany,DC=Local',
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
			->with('dc=mycompany,dc=local')
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
			['dn' => 'uid=testuser,ou=users,dc=mycompany,dc=local'],
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
			->with('uid=testuser,ou=users,dc=mycompany,dc=local')
			->willReturnSelf();

		$attrQueryBuilder->expects($this->once())
			->method('read')
			->willReturnSelf();

		// Mock attribute results (missing mail attribute)
		$attrResult = [
			'dn' => 'uid=testuser,ou=users,dc=mycompany,dc=local',
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
			->with('dc=mycompany,dc=local')
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
			['dn' => 'uid=testuser,ou=users,dc=mycompany,dc=local'],
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
			->with('uid=testuser,ou=users,dc=mycompany,dc=local')
			->willReturnSelf();

		$attrQueryBuilder->expects($this->once())
			->method('read')
			->willReturnSelf();

		// Mock attribute results (missing cn attribute)
		$attrResult = [
			'dn' => 'uid=testuser,ou=users,dc=mycompany,dc=local',
			'mail' => ['test@mycompany.local'],
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
