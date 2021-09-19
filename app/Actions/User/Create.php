<?php

namespace App\Actions\User;

use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;

class Create
{
	/**
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(array $data): User
	{
		if (User::query()->where('username', '=', $data['username'])->count()) {
			throw new InvalidPropertyException('username not unique');
		}
		try {
			$hashedPassword = bcrypt($data['password']);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Could not hash password', $e);
		}
		$user = new User();
		$user->upload = ($data['upload'] == '1');
		$user->lock = ($data['lock'] == '1');
		$user->username = $data['username'];
		$user->password = $hashedPassword;
		$user->save();

		return $user;
	}
}
