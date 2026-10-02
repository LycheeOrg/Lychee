<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\User;

use App\DTO\LdapUser;
use App\Exceptions\LdapAuthenticationException;
use App\Models\User;
use App\Repositories\ConfigManager;
use App\Services\Auth\LdapService;
use Illuminate\Support\Facades\DB;
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
		private readonly LdapService $ldap_service,
	) {
	}

	/**
	 * Find or create user, sync attributes and admin status.
	 *
	 * @param LdapUser $ldap_user LDAP authentication data
	 *
	 * @return User Local user (created or updated)
	 */
	public function do(LdapUser $ldap_user): User
	{
		Log::debug('Provisioning LDAP user', [
			'username' => $ldap_user->username,
			'dn' => config('app.debug', false) === true ? $ldap_user->user_dn : '***',
		]);

		// Step 1: Find or create user
		$user = $this->findOrCreateUser($ldap_user);
		$is_new_user = !$user->exists;

		// Step 2: Update user attributes from LDAP
		$this->updateUserAttributes($user, $ldap_user);

		// Step 3: Resolve admin status from LDAP groups (directory calls stay outside the transaction)
		$is_directory_admin = $this->isDirectoryAdmin($ldap_user->user_dn);

		// Step 4: Sync admin status and save in one transaction, so the
		// last-admin check cannot race with a concurrent demotion.
		DB::transaction(function () use ($user, $is_directory_admin): void {
			$this->syncAdminStatus($user, $is_directory_admin);
			$user->save();
		});

		Log::info('LDAP user provisioned', [
			'user_id' => $user->id,
			'username' => $user->username,
			'is_new' => $is_new_user,
			'is_admin' => $user->may_administrate,
		]);

		return $user;
	}

	/**
	 * Find existing user or create new one.
	 *
	 * @param LdapUser $ldap_user LDAP authentication data
	 *
	 * @return User Existing or new user (not yet saved)
	 */
	private function findOrCreateUser(LdapUser $ldap_user): User
	{
		// Try to find by username
		$user = User::query()->where('username', '=', $ldap_user->username)->first();

		if ($user !== null) {
			Log::debug('Found existing LDAP user', [
				'user_id' => $user->id,
				'username' => $user->username,
			]);

			return $user;
		}

		if (config('ldap.auth.auto_provision', true) !== true) {
			throw new LdapAuthenticationException('LDAP auto-provisioning is disabled for new users.');
		}

		Log::debug('Creating new LDAP user', ['username' => $ldap_user->username]);

		// Create new user
		$user = new User();
		$user->username = $ldap_user->username;

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
	 * @param User     $user      Local user to update
	 * @param LdapUser $ldap_user LDAP authentication data
	 */
	private function updateUserAttributes(User $user, LdapUser $ldap_user): void
	{
		// Ensure LDAP flag is set (for existing users that may have been created before this feature)
		$user->is_ldap = true;

		// Update email if provided by LDAP
		if ($ldap_user->email !== null) {
			$user->email = $ldap_user->email;
		}

		// Update display name if provided by LDAP
		if ($ldap_user->display_name !== null) {
			$user->display_name = $ldap_user->display_name;
		}
	}

	/**
	 * Whether the user belongs to the LDAP admin group.
	 *
	 * @param string $user_dn User's LDAP DN
	 */
	private function isDirectoryAdmin(string $user_dn): bool
	{
		$group_dns = $this->ldap_service->queryGroups($user_dn);

		return $this->ldap_service->isUserInAdminGroup($group_dns);
	}

	/**
	 * Sync admin status based on LDAP group membership.
	 * Must run inside the transaction that saves the user (see {@link self::mayDemote()}).
	 *
	 * @param User $user     Local user to update
	 * @param bool $is_admin Whether the user belongs to the LDAP admin group
	 */
	private function syncAdminStatus(User $user, bool $is_admin): void
	{
		if (!$is_admin && $user->may_administrate && !$this->mayDemote($user)) {
			// Never demote the owner or the last remaining admin through directory sync.
			return;
		}

		$user->may_administrate = $is_admin;
	}

	/**
	 * Whether removing the user's admin rights leaves the instance with its
	 * owner and at least one other admin.
	 *
	 * Every admin row is locked, so two admins demoted concurrently are
	 * serialized: the second waits for the first to commit, then no longer
	 * counts it as an admin.
	 */
	private function mayDemote(User $user): bool
	{
		if ($user->id === resolve(ConfigManager::class)->getValueAsInt('owner_id')) {
			return false;
		}

		$admin_ids = User::query()->where('may_administrate', '=', true)->lockForUpdate()->pluck('id')->all();

		return count(array_diff($admin_ids, [$user->id])) > 0;
	}
}
