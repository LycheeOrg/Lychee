<?php

namespace App\Actions\User;

use App\Exceptions\JsonError;
use App\Models\User;

class Create
{
	/**
	 * @throws JsonError
	 */
	public function do(array $data): User
	{
		if (User::query()->where('username', '=', $data['username'])->count()) {
			throw new JsonError('username must be unique');
		}

		$user = new User();
		$user->may_upload = $data['may_upload'];
		$user->is_locked = $data['is_locked'];
		$user->username = $data['username'];
		$user->password = bcrypt($data['password']);
		if (!$user->save()) {
			throw new \RuntimeException('could not save new user');
		}

		return $user;
	}
}
