<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
	use HandlesAuthorization;

	public const IS_ADMIN = 'isAdmin';
	public const CAN_UPLOAD = 'canUpload';
	public const CAN_EDIT_SETTINGS = 'canEditSettings';

	/**
	 * Perform pre-authorization checks.
	 *
	 * @param \App\Models\User $user
	 * @param string           $ability
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
	 * This defines if the user is admin.
	 *
	 * @param User|null $user
	 *
	 * @return bool
	 */
	public function isAdmin(?User $user): bool
	{
		return $user?->id === 0;
	}

	/**
	 * This defines if user can edit their settings.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canEditSettings(User $user): bool
	{
		return !$user->is_locked;
	}

	/**
	 * This defines if user has upload rights.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function canUpload(User $user): bool
	{
		return $user->may_upload;
	}
}
