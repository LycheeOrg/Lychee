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
		if (optional($user)->id === 0) {
			return true;
		}
	}

	public function edit(User $user): bool
	{
		return !$user->is_locked;
	}

	public function upload(User $user): bool
	{
		return !$user->may_upload;
	}
}
