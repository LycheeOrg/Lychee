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
		$user->upload = ($data['upload'] == '1');
		$user->lock = ($data['lock'] == '1');
		if (isset($data['password'])) {
			$user->password = bcrypt($data['password']);
		}

		return $user->save();
	}
}
