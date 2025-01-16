<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\User;

use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TokenDisable
{
	/**
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(): User
	{
		/** @var User $user */
		$user = Auth::user();
		$user->token = null;
		$user->save();

		return $user;
	}
}
