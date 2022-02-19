<?php

namespace App\Http\Requests\Contracts;

use App\Models\User;

interface HasUser
{
	public const USER_ID_ATTRIBUTE = 'userID';

	/**
	 * @return User
	 */
	public function user(): User;
}
