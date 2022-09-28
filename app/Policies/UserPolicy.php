<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BasePolicy
{
	public const CAN_EDIT_OWN_SETTINGS = 'canEditOwnSettings';
	public const CAN_CREATE_OR_EDIT_OR_DELETE = 'canCreateOrEditOrDelete';
	public const CAN_LIST = 'canList';

	/**
	 * This defines if user can edit their settings.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canEditOwnSettings(User $user): bool
	{
		return $user->may_edit_own_settings;
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
}
