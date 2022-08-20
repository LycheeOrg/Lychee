<?php

namespace App\Actions\Settings;

use App\Exceptions\ModelDBException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class SetLogin
{
	/**
	 * This is only used to set the Admin login.
	 * No verification are applied.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return User the updated user model for the admin
	 *
	 * @throws ModelNotFoundException
	 * @throws ModelDBException
	 */
	public function do(string $username, string $password): User
	{
		/** @var User $adminUser */
		$adminUser = User::query()->findOrFail(0);
		$adminUser->username = $username;
		$adminUser->password = Hash::make($password);
		$adminUser->is_locked = false;
		$adminUser->may_upload = true;
		$adminUser->save();

		return $adminUser;
	}
}
