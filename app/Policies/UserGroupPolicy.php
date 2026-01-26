<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Enum\UserGroupRole;
use App\Models\User;
use App\Models\UserGroup;

/**
 * This class has a DUAL purpose:.
 *
 * 1. Define the Rights of the current user over managing Users.
 * 2. Define the Rights of the current user with regard to what it can modify on its profile.
 */
class UserGroupPolicy extends BasePolicy
{
	public const CAN_CREATE = 'canCreateOrDelete';
	public const CAN_READ = 'canRead';
	public const CAN_EDIT = 'canEdit';
	public const CAN_DELETE = 'canCreateOrDelete';
	public const CAN_ADD_OR_REMOVE_USER = 'canAddOrRemoveUser';
	public const CAN_LIST = 'canList';

	public function canCreateOrDelete(User $user): bool
	{
		// Note, the administrator is already handled in the `before()` method and every one else is not allowed to create/delete users.
		return $user->may_administrate;
	}

	/**
	 * This defines if a user can list the users of a groups.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canRead(User $user, UserGroup $user_group): bool
	{
		// If the user is part of the group, it can read it.
		return $user_group->users()->where('user_id', '=', $user->id)->exists();
	}

	/**
	 * This defines if the user can list the user-groups.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canList(User $user): bool
	{
		return $user->may_upload ||
			// Check if user belongs to a group and is admin.
			$user->user_groups()->wherePivot('role', 'admin')->exists();
	}

	/**
	 * This defines if a user can edit the user group.
	 *
	 * @param User       $user
	 * @param ?UserGroup $user_group
	 *
	 * @return bool
	 */
	public function canEdit(User $user, ?UserGroup $user_group = null): bool
	{
		if ($user_group === null) {
			return $user->user_groups()->wherePivot('role', 'admin')->exists();
		}

		// Check if the user has the 'admin' role in the user group using the UserGroupRole enum
		return $user_group->users()->where('user_id', $user->id)->wherePivot('role', UserGroupRole::ADMIN->value)->exists();
	}

	/**
	 * This defines if a user can add another user to the group.
	 *
	 * @param User      $user
	 * @param UserGroup $user_group
	 *
	 * @return bool
	 */
	public function canAddOrRemoveUser(User $user, UserGroup $user_group): bool
	{
		// Check if the user has the 'admin' role in the user group using the UserGroupRole enum
		return $user_group->users()->where('user_id', $user->id)->wherePivot('role', UserGroupRole::ADMIN->value)->exists();
	}
}
