<?php

namespace App\Http\Requests\Contracts;

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
