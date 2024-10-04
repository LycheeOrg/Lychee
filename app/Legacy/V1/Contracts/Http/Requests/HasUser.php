<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Models\User;

interface HasUser extends HasOptionalUser
{
	/**
	 * {@inheritDoc}
	 *
	 * @return User
	 */
	public function user2(): User;
}
