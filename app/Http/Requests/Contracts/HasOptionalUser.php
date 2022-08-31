<?php

namespace App\Http\Requests\Contracts;

use App\Models\User;

interface HasOptionalUser
{
	public const USER_ID_ATTRIBUTE = 'userID';

	/**
	 * Returns an _additional_ {@link User} object associated with this request.
	 *
	 * This method is called `user2`, because Laravel already defines
	 * {@link \Illuminate\Http\Request::user()} which returns the user which
	 * is currently authenticated within the HTTP session.
	 * This method returns another user object which is explicitly part of the
	 * request.
	 *
	 * @return User|null
	 */
	public function user2(): ?User;
}
