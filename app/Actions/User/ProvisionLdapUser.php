<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\User;

use App\DTO\LdapUser;
use App\Models\User;
use App\Services\Auth\LdapService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Provision (create or update) local User from LDAP authentication data.
 *
 * Called after successful LDAP authentication to ensure local user exists
 * and has up-to-date attributes and permissions from LDAP.
 */
class ProvisionLdapUser
{
	public function __construct(
		private readonly LdapService $ldapService,
	) {
	}

	/**
	 * Find or create user, sync attributes and admin status.
	 *
	 * @param LdapUser $ldapUser LDAP authentication data
	 *
	 * @return User Local user (created or updated)
	 */
	public function do(LdapUser $ldapUser): User
	{
		Log::debug('Provisioning LDAP user', [
			'username' => $ldapUser->username,
			'dn' => $ldapUser->userDn,
		]);

		// Step 1: Find or create user
		$user = $this->findOrCreateUser($ldapUser);
		$isNewUser = !$user->exists;

		// Step 2: Update user attributes from LDAP
		$this->updateUserAttributes($user, $ldapUser);

		// Step 3: Sync admin status based on LDAP groups
		$this->syncAdminStatus($user, $ldapUser->userDn);

		// Save changes
		$user->save();

		Log::info('LDAP user provisioned', [
			'user_id' => $user->id,
			'username' => $user->username,
			'is_new' => $isNewUser,
			'is_admin' => $user->may_administrate,
		]);

		return $user;
	}

	/**
	 * Find existing user or create new one.
	 *
	 * @param LdapUser $ldapUser LDAP authentication data
	 *
	 * @return User Existing or new user (not yet saved)
	 */
	private function findOrCreateUser(LdapUser $ldapUser): User
	{
		// Try to find by username
		$user = User::query()->where('username', '=', $ldapUser->username)->first();

		if ($user !== null) {
			Log::debug('Found existing LDAP user', [
				'user_id' => $user->id,
				'username' => $user->username,
			]);

			return $user;
		}

		Log::debug('Creating new LDAP user', ['username' => $ldapUser->username]);

		// Create new user
		$user = new User();
		$user->username = $ldapUser->username;

		// Set random password (user authenticates via LDAP, not local password)
		$user->password = Hash::make(bin2hex(random_bytes(32)));

		// Mark as LDAP-managed user
		$user->is_ldap = true;

		// Default permissions for LDAP users
		$user->may_upload = true;
		$user->may_edit_own_settings = true;
		$user->may_administrate = false; // Will be set by syncAdminStatus

		return $user;
	}

	/**
	 * Update user attributes from LDAP data.
	 *
	 * @param User     $user     Local user to update
	 * @param LdapUser $ldapUser LDAP authentication data
	 */
	private function updateUserAttributes(User $user, LdapUser $ldapUser): void
	{
		// Ensure LDAP flag is set (for existing users that may have been created before this feature)
		$user->is_ldap = true;

		// Update email if provided by LDAP
		if ($ldapUser->email !== null) {
			$user->email = $ldapUser->email;
		}

		// Update display name if provided by LDAP
		if ($ldapUser->display_name !== null) {
			$user->display_name = $ldapUser->display_name;
		}
	}

	/**
	 * Sync admin status based on LDAP group membership.
	 *
	 * @param User   $user   Local user to update
	 * @param string $userDn User's LDAP DN
	 */
	private function syncAdminStatus(User $user, string $userDn): void
	{
		// Query user's LDAP groups
		$groupDns = $this->ldapService->queryGroups($userDn);

		// Check if user is in admin group
		$isAdmin = $this->ldapService->isUserInAdminGroup($groupDns);

		// Update admin flag
		$user->may_administrate = $isAdmin;
	}
}
