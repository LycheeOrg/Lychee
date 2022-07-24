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
	 * This policy gate is unused for now.
	 * However it should later take care of checking whether current user has edit rights or not.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function edit(User $user): bool
	{
		return !$user->is_locked;
	}

	/**
	 * This policy gate is unsued for now.
	 * However it should later be used to check if user has upload rights or not.
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
