<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
	use HandlesAuthorization;

	public const IS_ADMIN = 'isAdmin';
	public const CAN_EDIT_OWN_SETTINGS = 'canEditOwnSettings';

	/**
	 * This defines if the user is admin.
	 *
	 * @param User|null $user
	 *
	 * @return bool
	 */
	public function isAdmin(?User $user): bool
	{
		return $user?->may_administrate === true;
	}

	/**
	 * Perform pre-authorization checks.
	 *
	 * @param User|null $user
	 * @param string    $ability
	 *
	 * @return void|bool
	 */
	public function before(?User $user, $ability)
	{
		if ($this->isAdmin($user)) {
			return true;
		}
	}

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
}
