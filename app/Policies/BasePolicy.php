<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
	use HandlesAuthorization;

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
		if ($user?->may_administrate === true) {
			return true;
		}
	}
}