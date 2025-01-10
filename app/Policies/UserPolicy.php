<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

use App\Models\User;

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
		return $user->may_edit_own_settings;
	}
}
