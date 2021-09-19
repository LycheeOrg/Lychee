<?php

namespace App\Actions\User;

use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;

class Save
{
	/**
	 * @throws ModelDBException
	 * @throws InvalidPropertyException
	 */
	public function do(User $user, array $data): User
	{
		if (User::query()
			->where('username', '=', $data['username'])
			->where('id', '!=', $data['id'])
			->count()
		) {
			throw new InvalidPropertyException('username not unique');
		}
		try {
			$hashedPassword = bcrypt($data['password']);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Could not hash password');
		}

		$user->username = $data['username'];
		$user->upload = ($data['upload'] == '1');
		$user->lock = ($data['lock'] == '1');
		if (isset($data['password'])) {
			$user->password = $hashedPassword;
		}
		$user->save();

		return $user;
	}
}
