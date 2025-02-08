<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

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
	public const CAN_CREATE = 'canCreateOrEditOrDelete';
	public const CAN_EDIT = 'canCreateOrEditOrDelete';
	public const CAN_DELETE = 'canCreateOrEditOrDelete';
	public const CAN_ADD_OR_REMOVE_USER = 'canAddOrRemoveUser';
	public const CAN_LIST = 'canList';

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
	 * This defines if a user can add another user to the group.
	 *
	 * @param User      $user
	 * @param UserGroup $userGroup
	 *
	 * @return bool
	 */
	public function canAddOrRemoveUser(User $user, UserGroup $userGroup): bool
	{
		return false;
	}
}
