<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Policies;

use App\Models\User;

class TagPolicy extends BasePolicy
{
	public const CAN_LIST = 'canList';
	public const CAN_EDIT = 'canEdit';

	/**
	 * Determine whether the user can list tags.
	 */
	public function canList(User $user): bool
	{
		return true;
	}

	/**
	 * Users with upload rights are allowed to delete, edit, merge their own tags.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canEdit(User $user): bool
	{
		return $user->may_upload;
	}
}
