<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
	 * This method returns another user object which is explicitly part of the
	 * request.
	 *
	 * @return User
	 */
	public function user2(): User
	{
		return $this->user2;
	}
}