<?php

namespace App\Http\Requests\Traits;

use App\Models\User;

trait HasUserTrait
{
	/**
	 * @var User
	 */
	protected User $user;

	/**
	 * @return User
	 */
	public function user(): User
	{
		return $this->user;
	}
}