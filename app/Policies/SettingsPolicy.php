<?php

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
	public const CAN_CLEAR_LOGS = 'canClearLogs';
	public const CAN_SEE_DIAGNOSTICS = 'canSeeDiagnostics';
	public const CAN_UPDATE = 'canUpdate';

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
		return $user !== null && $user->may_administrate;
	}

	/**
	 * This function returns false as it is bypassed by the before()
	 * which directly checks for admin rights.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canClearLogs(User $user): bool
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
	public function canUpdate(User $user): bool
	{
		return false;
	}
}
