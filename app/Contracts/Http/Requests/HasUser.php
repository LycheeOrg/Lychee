<?php

namespace App\Contracts\Http\Requests;

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
