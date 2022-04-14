<?php

namespace App\Http\Requests\Traits;

use App\Models\User;

trait HasUserTrait
{
	/**
	 * @var User
	 */
	protected User $user2;

	/**
	 * Returns an _additional_ {@link User} object associated with this request.
	 *
	 * This method is called `user2`, because Laravel already defines
	 * {@link \Illuminate\Http\Request::user()} which returns the user which
	 * is currently authenticated within the HTTP session.
	 * This method returns another user object which is part of the request
	 * and shall be manipulated as part of the user management.
	 *
	 * @return User
	 */
	public function user2(): User
	{
		return $this->user2;
	}
}