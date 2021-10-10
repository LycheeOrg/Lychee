<?php

namespace App\Actions\User;

use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Http\Requests\Traits\HasPasswordTrait;
use App\Models\User;

class Save
{
	/**
	 * @param User        $user
	 * @param string      $username
	 * @param string|null $password  see {@link HasPasswordTrait::password()} for the difference between the values `''` and `null`
	 * @param bool        $mayUpload
	 * @param bool        $isLocked
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(User $user, string $username, ?string $password, bool $mayUpload, bool $isLocked): void
	{
		if (User::query()
			->where('username', '=', $username)
			->where('id', '!=', $user->id)
			->count()
		) {
			throw new InvalidPropertyException('username not unique');
		}

		$user->username = $username;
		$user->upload = $mayUpload;
		$user->lock = $isLocked;
		if ($password !== null) {
			try {
				$user->password = bcrypt($password);
			} catch (\InvalidArgumentException $e) {
				throw new InvalidPropertyException('Could not hash password');
			}
		}
		$user->save();
	}
}
