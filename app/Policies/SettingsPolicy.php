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
	public const CAN_USE_2FA = 'canUse2FA';
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
	 * This function returns false as it is bypassed by the before()
	 * which directly checks for admin rights.
	 *
	 * TODO: Later we will want to use this function to allow users
	 * to make use of 2FA as opposed to only the admin for now.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canUse2FA(User $user): bool
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
	public function canSeeLogs(User $user): bool
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
