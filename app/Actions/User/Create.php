<?php

namespace App\Actions\User;

use App\Exceptions\JsonError;
use App\Models\User;

class Create
{
	public function do(array $data): bool
	{
		if (User::where('username', '=', $data['username'])->count()) {
			throw new JsonError('username must be unique');
		}

		$user = new User();
		$user->upload = ($data['upload'] == '1');
		$user->lock = ($data['lock'] == '1');
		$user->username = $data['username'];
		$user->password = bcrypt($data['password']);

		return @$user->save();
	}
}
