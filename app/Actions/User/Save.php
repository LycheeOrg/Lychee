<?php

namespace App\Actions\User;

use App\Exceptions\JsonError;
use App\Models\User;

class Save
{
	public function do(User $user, array $data): bool
	{
		if (User::where('username', '=', $data['username'])->where('id', '!=', $data['id'])->count()) {
			throw new JsonError('username must be unique');
		}

		// check for duplicate name here !
		$user->username = $data['username'];
		$user->may_upload = $data['may_upload'];
		$user->is_locked = $data['is_locked'];
		if (isset($data['password'])) {
			$user->password = bcrypt($data['password']);
		}

		return $user->save();
	}
}
