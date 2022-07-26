<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
	use HandlesAuthorization;

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
		if ($user?->isAdmin()) {
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
	public function editSettings(User $user): bool
	{
		return !$user->is_locked;
	}

	/**
	 * This defines is user has upload rights.
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
