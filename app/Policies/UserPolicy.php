<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
	use HandlesAuthorization;

	public const ADMIN = 'admin';
	public const CAN_UPLOAD = 'upload';
	public const EDIT_SETTINGS = 'editSettings';

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
		if ($this->admin($user)) {
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
	public function admin(?User $user): bool
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
	public function editSettings(User $user): bool
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
	public function upload(User $user): bool
	{
		return $user->may_upload;
	}
}
