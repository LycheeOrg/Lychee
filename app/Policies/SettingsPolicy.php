<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Policies;

use App\Models\User;

/**
 * Most of those will return false as they are handle by the before()
 * which checks for admin rights.
 */
class SettingsPolicy extends BasePolicy
{
	public const CAN_EDIT = 'canEdit';
	public const CAN_SEE_LOGS = 'canSeeLogs';
	public const CAN_SEE_DIAGNOSTICS = 'canSeeDiagnostics';
	public const CAN_SEE_STATISTICS = 'canSeeStatistics';
	public const CAN_UPDATE = 'canUpdate';
	public const CAN_ACCESS_DEV_TOOLS = 'canAccessDevTools';

	/**
	 * This function returns false as it is bypassed by the before()
	 * which directly checks for admin rights.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canEdit(User $user): bool
	{
		return false;
	}

	/**
	 * We are allowed to see the logs if we are not logged in and if there are no Admins.
	 *
	 * @param ?User $user
	 *
	 * @return bool
	 */
	public function canSeeLogs(?User $user): bool
	{
		return $user?->may_administrate === true;
	}

	/**
	 * This function returns false as it is bypassed by the before()
	 * which directly checks for admin rights.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canSeeDiagnostics(User $user): bool
	{
		return false;
	}

	/**
	 * This function returns false as it is bypassed by the before()
	 * which directly checks for admin rights.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canSeeStatistics(User $user): bool
	{
		return true;
	}

	/**
	 * This function returns false as it is bypassed by the before()
	 * which directly checks for admin rights.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canUpdate(User $user): bool
	{
		return false;
	}

	/**
	 * This function returns false as it is bypassed by the before()
	 * which directly checks for admin rights.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canAccessDevTools(User $user): bool
	{
		return false;
	}
}
