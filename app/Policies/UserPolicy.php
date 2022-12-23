<?php

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
	public const CAN_USE_2FA = 'canUse2FA';

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
