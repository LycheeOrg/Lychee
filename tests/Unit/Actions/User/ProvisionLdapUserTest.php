<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Actions\User;

use App\Actions\User\ProvisionLdapUser;
use App\DTO\LdapUser;
use App\Models\User;
use App\Services\Auth\LdapService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Unit tests for ProvisionLdapUser action.
 *
 * Tests user provisioning logic (create/update from LDAP data).
 */
class ProvisionLdapUserTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private ProvisionLdapUser $action;
	private LdapService $ldapService;

	protected function setUp(): void
	{
		parent::setUp();

		// Mock LdapService
		$this->ldapService = $this->createMock(LdapService::class);
		$this->action = new ProvisionLdapUser($this->ldapService);
	}

	public function testCreateNewUser(): void
	{
		// Mock LDAP user data
		$ldapUser = new LdapUser(
			username: 'testuser',
			user_dn: 'uid=testuser,ou=users,dc=test,dc=local',
			email: 'test@example.com',
			display_name: 'Test User'
		);

		// Mock group query (no groups)
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->with('uid=testuser,ou=users,dc=test,dc=local')
			->willReturn([]);

		// Mock admin check (not admin)
		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->with([])
			->willReturn(false);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify user was created
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame('testuser', $user->username);
		$this->assertSame('test@example.com', $user->email);
		$this->assertSame('Test User', $user->display_name);
		$this->assertTrue($user->may_upload);
		$this->assertTrue($user->may_edit_own_settings);
		$this->assertFalse($user->may_administrate);
		$this->assertNotNull($user->password); // Random password set
	}

	public function testUpdateExistingUser(): void
	{
		// Create existing user
		$existingUser = new User();
		$existingUser->username = 'testuser';
		$existingUser->email = 'old@example.com';
		$existingUser->display_name = 'Old Name';
		$existingUser->password = \Illuminate\Support\Facades\Hash::make('oldpassword');
		$existingUser->may_upload = true;
		$existingUser->may_edit_own_settings = true;
		$existingUser->may_administrate = false;
		$existingUser->save();

		// Mock LDAP user data with updated attributes
		$ldapUser = new LdapUser(
			username: 'testuser',
			user_dn: 'uid=testuser,ou=users,dc=test,dc=local',
			email: 'new@example.com',
			display_name: 'New Name'
		);

		// Mock group query (no groups)
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->with('uid=testuser,ou=users,dc=test,dc=local')
			->willReturn([]);

		// Mock admin check (not admin)
		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->with([])
			->willReturn(false);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify user was updated
		$this->assertSame($existingUser->id, $user->id);
		$this->assertSame('testuser', $user->username);
		$this->assertSame('new@example.com', $user->email);
		$this->assertSame('New Name', $user->display_name);
		$this->assertFalse($user->may_administrate);
	}

	public function testSyncAdminStatusTrue(): void
	{
		// Mock LDAP user data
		$ldapUser = new LdapUser(
			username: 'adminuser',
			user_dn: 'uid=adminuser,ou=users,dc=test,dc=local',
			email: 'admin@example.com',
			display_name: 'Admin User'
		);

		// Mock group query (user in admin group)
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->with('uid=adminuser,ou=users,dc=test,dc=local')
			->willReturn(['cn=admins,ou=groups,dc=test,dc=local']);

		// Mock admin check (is admin)
		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->with(['cn=admins,ou=groups,dc=test,dc=local'])
			->willReturn(true);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify user is admin
		$this->assertTrue($user->may_administrate);
	}

	public function testHandleMissingAttributes(): void
	{
		// Create existing user with email to verify it's not overwritten
		$existingUser = new User();
		$existingUser->username = 'testuser';
		$existingUser->email = 'test@example.com';
		$existingUser->display_name = 'Test User';
		$existingUser->password = \Illuminate\Support\Facades\Hash::make('password');
		$existingUser->may_upload = true;
		$existingUser->may_edit_own_settings = true;
		$existingUser->may_administrate = false;
		$existingUser->save();

		// Mock LDAP user data with missing attributes
		$ldapUser = new LdapUser(
			username: 'testuser',
			user_dn: 'uid=testuser,ou=users,dc=test,dc=local',
			email: null,
			display_name: null
		);

		// Mock group query (no groups)
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->with('uid=testuser,ou=users,dc=test,dc=local')
			->willReturn([]);

		// Mock admin check (not admin)
		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->with([])
			->willReturn(false);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify existing attributes are preserved when LDAP has null
		$this->assertSame('testuser', $user->username);
		$this->assertSame('test@example.com', $user->email);
		$this->assertSame('Test User', $user->display_name);
	}

	public function testCreateUserWithNullEmail(): void
	{
		// Mock LDAP user data without email
		$ldapUser = new LdapUser(
			username: 'nomail',
			user_dn: 'uid=nomail,ou=users,dc=test,dc=local',
			email: null,
			display_name: 'No Mail User'
		);

		// Mock group query
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->willReturn([]);

		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->willReturn(false);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify user created with null email
		$this->assertSame('nomail', $user->username);
		$this->assertNull($user->email);
		$this->assertSame('No Mail User', $user->display_name);
	}

	public function testCreateUserWithNullDisplayName(): void
	{
		// Mock LDAP user data without display name
		$ldapUser = new LdapUser(
			username: 'nodisplay',
			user_dn: 'uid=nodisplay,ou=users,dc=test,dc=local',
			email: 'nodisplay@example.com',
			display_name: null
		);

		// Mock group query
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->willReturn([]);

		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->willReturn(false);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify user created with username as fallback for display_name
		$this->assertSame('nodisplay', $user->username);
		$this->assertSame('nodisplay@example.com', $user->email);
		$this->assertNull($user->display_name);
	}

	public function testAdminUserLosesAdminRights(): void
	{
		// Create existing admin user
		$existingUser = new User();
		$existingUser->username = 'demoted';
		$existingUser->email = 'demoted@example.com';
		$existingUser->display_name = 'Demoted User';
		$existingUser->password = \Illuminate\Support\Facades\Hash::make('password');
		$existingUser->may_upload = true;
		$existingUser->may_edit_own_settings = true;
		$existingUser->may_administrate = true; // Was admin
		$existingUser->save();

		// Mock LDAP user data
		$ldapUser = new LdapUser(
			username: 'demoted',
			user_dn: 'uid=demoted,ou=users,dc=test,dc=local',
			email: 'demoted@example.com',
			display_name: 'Demoted User'
		);

		// Mock group query (no admin group)
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->willReturn(['cn=users,ou=groups,dc=test,dc=local']);

		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->with(['cn=users,ou=groups,dc=test,dc=local'])
			->willReturn(false);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify admin rights were removed
		$this->assertFalse($user->may_administrate);
	}

	public function testRegularUserGainsAdminRights(): void
	{
		// Create existing regular user
		$existingUser = new User();
		$existingUser->username = 'promoted';
		$existingUser->email = 'promoted@example.com';
		$existingUser->display_name = 'Promoted User';
		$existingUser->password = \Illuminate\Support\Facades\Hash::make('password');
		$existingUser->may_upload = true;
		$existingUser->may_edit_own_settings = true;
		$existingUser->may_administrate = false; // Was not admin
		$existingUser->save();

		// Mock LDAP user data
		$ldapUser = new LdapUser(
			username: 'promoted',
			user_dn: 'uid=promoted,ou=users,dc=test,dc=local',
			email: 'promoted@example.com',
			display_name: 'Promoted User'
		);

		// Mock group query (now in admin group)
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->willReturn(['cn=admins,ou=groups,dc=test,dc=local']);

		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->with(['cn=admins,ou=groups,dc=test,dc=local'])
			->willReturn(true);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify admin rights were granted
		$this->assertTrue($user->may_administrate);
	}

	public function testUserWithMultipleGroups(): void
	{
		// Mock LDAP user data
		$ldapUser = new LdapUser(
			username: 'multigroup',
			user_dn: 'uid=multigroup,ou=users,dc=test,dc=local',
			email: 'multi@example.com',
			display_name: 'Multi Group User'
		);

		// Mock group query (multiple groups)
		$groups = [
			'cn=users,ou=groups,dc=test,dc=local',
			'cn=developers,ou=groups,dc=test,dc=local',
			'cn=admins,ou=groups,dc=test,dc=local',
		];
		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->willReturn($groups);

		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->with($groups)
			->willReturn(true);

		// Provision user
		$user = $this->action->do($ldapUser);

		// Verify admin status from multiple groups
		$this->assertTrue($user->may_administrate);
	}

	public function testPasswordIsRandomForNewUsers(): void
	{
		// Mock LDAP user data
		$ldapUser = new LdapUser(
			username: 'newrandom',
			user_dn: 'uid=newrandom,ou=users,dc=test,dc=local',
			email: 'random@example.com',
			display_name: 'Random Pass User'
		);

		$this->ldapService->expects($this->once())
			->method('queryGroups')
			->willReturn([]);

		$this->ldapService->expects($this->once())
			->method('isUserInAdminGroup')
			->willReturn(false);

		// Provision user
		$user = $this->action->do($ldapUser);
		$password1 = $user->password;

		// Verify password is set and hashed
		$this->assertNotNull($password1);
		$this->assertNotSame('', $password1);
		$this->assertStringStartsWith('$2y$', $password1); // BCrypt hash
		$this->assertGreaterThan(50, strlen($password1)); // Hashed passwords are long
	}
}
