<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class BasePolicy
{
	use HandlesAuthorization;

	public const IS_ADMIN = 'isAdmin';

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
	 * In some.
	 *
	 * @return bool
	 */
	public function isAdmin(?User $user = null): bool
	{
		return ($user ?? Auth::user())?->may_administrate === true;
	}
}