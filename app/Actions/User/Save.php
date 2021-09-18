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
			$user->username = $data['username'];
			$user->upload = ($data['upload'] == '1');
			$user->lock = ($data['lock'] == '1');
			if (isset($data['password'])) {
				$user->password = bcrypt($data['password']);
			}
			$success = $user->save();
		} catch (\Throwable $e) {
			throw ModelDBException::create('user', 'update', $e);
		}
		if (!$success) {
			throw ModelDBException::create('user', 'update');
		}

		return $user;
	}
}
