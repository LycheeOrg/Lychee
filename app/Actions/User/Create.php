<?php

namespace App\Actions\User;

use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;

class Create
{
	/**
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(string $username, string $password, bool $mayUpload, bool $isLocked, string $email = null, string $fullname = null): User
	{
		if (User::query()->where('username', '=', $username)->count()) {
			throw new ConflictingPropertyException('Username already exists');
		}
		try {
			$user = new User();
			$user->may_upload = $mayUpload;
			$user->is_locked = $isLocked;
			$user->username = $username;
			$user->password = bcrypt($password);
			$user->email = $email;
			$user->fullname = $fullname;
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Could not hash password', $e);
		}
		$user->save();

		return $user;
	}
}
