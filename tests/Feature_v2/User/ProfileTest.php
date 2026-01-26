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

namespace Tests\Feature_v2\User;

use App\Models\User;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ProfileTest extends BaseApiWithDataTest
{
	// Test update as guest.
	public function testUpdateLoginGuest(): void
	{
		$response = $this->postJson('Profile::update', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password3',
		]);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password2',
		]);
		$this->assertUnauthorized($response);
	}

	public function testSetEmailGuest(): void
	{
		$response = $this->postJson('Profile::update', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Profile::update', [
			'username' => 'username',
			'email' => 'something@something.com',
			'old_password' => 'passwordpasswordpassword',
		]);
		$this->assertUnauthorized($response);
	}

	public function testUnsetResetTokenGuest(): void
	{
		$response = $this->postJson('Profile::resetToken', []);
		$this->assertUnauthorized($response);

		$response = $this->postJson('Profile::unsetToken', []);
		$this->assertUnauthorized($response);
	}

	public function testUserLocked(): void
	{
		$response = $this->actingAs($this->userLocked)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password2',
			'email' => '',
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::update', [
			'username' => 'username',
			'email' => 'something@something.com',
			'old_password' => 'password',
		]);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::resetToken', []);
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->postJson('Profile::unsetToken', []);
		$this->assertForbidden($response);
	}

	public function testUserUnlocked(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password3',
		]);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'username',
			'password' => 'password2',
			'password_confirmation' => 'password2',
			'email' => '',
		]);
		$this->assertCreated($response);

		$response = $this->postJson('Auth::logout', []);
		$this->assertNoContent($response);

		$response = $this->postJson('Auth::login', [
			'username' => 'username',
			'password' => 'password2',
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::update', [
			'old_password' => 'password2',
			'username' => 'username',
			'email' => 'something@something.com',
		]);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::resetToken', []);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Profile::unsetToken', []);
		$this->assertNoContent($response);
	}

	public function testLdapUserCannotUpdateUsername(): void
	{
		// Create an LDAP user
		$ldapUser = User::factory()->create([
			'username' => 'ldapuser',
			'password' => bcrypt('password'),
			'is_ldap' => true,
			'may_edit_own_settings' => true,
		]);

		// Attempt to update username - should be forbidden
		$response = $this->actingAs($ldapUser)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'newusername',
			'email' => '',
		]);
		$this->assertForbidden($response);
	}

	public function testLdapUserCannotUpdatePassword(): void
	{
		// Create an LDAP user
		$ldapUser = User::factory()->create([
			'username' => 'ldapuser',
			'password' => bcrypt('password'),
			'is_ldap' => true,
			'may_edit_own_settings' => true,
		]);

		// Attempt to update password - should be forbidden
		$response = $this->actingAs($ldapUser)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'ldapuser',
			'password' => 'newpassword',
			'password_confirmation' => 'newpassword',
			'email' => '',
		]);
		$this->assertForbidden($response);
	}

	public function testLdapUserCannotResetToken(): void
	{
		// Create an LDAP user
		$ldapUser = User::factory()->create([
			'username' => 'ldapuser',
			'password' => bcrypt('password'),
			'is_ldap' => true,
			'may_edit_own_settings' => true,
		]);

		// Attempt to reset token - should be forbidden
		$response = $this->actingAs($ldapUser)->postJson('Profile::resetToken', []);
		$this->assertForbidden($response);
	}

	public function testLdapUserCannotUnsetToken(): void
	{
		// Create an LDAP user
		$ldapUser = User::factory()->create([
			'username' => 'ldapuser',
			'password' => bcrypt('password'),
			'is_ldap' => true,
			'may_edit_own_settings' => true,
		]);

		// Attempt to unset token - should be forbidden
		$response = $this->actingAs($ldapUser)->postJson('Profile::unsetToken', []);
		$this->assertForbidden($response);
	}

	public function testNonLdapUserCanStillUpdateProfile(): void
	{
		// Verify that non-LDAP users can still update their profile
		$regularUser = User::factory()->create([
			'username' => 'regularuser',
			'password' => bcrypt('password'),
			'is_ldap' => false,
			'may_edit_own_settings' => true,
		]);

		// Should succeed
		$response = $this->actingAs($regularUser)->postJson('Profile::update', [
			'old_password' => 'password',
			'username' => 'newregularuser',
			'password' => 'newpassword',
			'password_confirmation' => 'newpassword',
			'email' => '',
		]);
		$this->assertCreated($response);
	}
}