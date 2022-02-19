<?php

namespace App\Http\Requests\Contracts;

use App\Models\User;

interface HasUser
{
	public const USER_ID_ATTRIBUTE = 'userID';

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
	public function user2(): User;
}
