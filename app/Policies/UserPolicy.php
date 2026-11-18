<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Models\User;
use App\Repositories\ConfigManager;

/**
 * This class has a DUAL purpose:.
 *
 * 1. Define the Rights of the current user over managing Users.
 * 2. Define the Rights of the current user with regard to what it can modify on its profile.
 */
class UserPolicy extends BasePolicy
{
	public const CAN_EDIT = 'canEdit';
	public const CAN_CREATE_OR_EDIT_OR_DELETE = 'canCreateOrEditOrDelete';
	public const CAN_LIST = 'canList';
	public const CAN_EDIT_SERVER_PATH = 'canEditServerPath';

	/**
	 * Admins skip every ability except {@link UserPolicy::CAN_EDIT_SERVER_PATH},
	 * which is reserved to the server owner (Feature 115, FR-115-19).
	 *
	 * @param User|null $user
	 * @param string    $ability
	 *
	 * @return void|bool
	 */
	public function before(?User $user, $ability)
	{
		if ($ability === self::CAN_EDIT_SERVER_PATH) {
			return $user?->id === app(ConfigManager::class)->getValueAsInt('owner_id');
		}

		return parent::before($user, $ability);
	}

	/**
	 * Only the server owner may grant, change or revoke a user's `server_path`
	 * (Feature 115, FR-115-19): other admins must not widen anyone's access to the file system.
	 */
	public function canEditServerPath(User $user): bool
	{
		return false;
	}

	public function canCreateOrEditOrDelete(User $user): bool
	{
		// Note, the administrator is already handled in the `before()` method and every one else is not allowed to create/delete users.
		return false;
	}

	public function canList(User $user): bool
	{
		return $user->may_upload;
	}

	/**
	 * This defines if user can edit their settings.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canEdit(User $user): bool
	{
		return $user->may_edit_own_settings && !$user->is_ldap;
	}
}
