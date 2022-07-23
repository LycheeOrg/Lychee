<?php

namespace App\Actions\Settings;

use App\Models\User;
use Hash;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class SetLogin
{
	/**
	 * This is only used to set the Admin login.
	 * No verification are applied.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 * @throws InvalidArgumentException
	 * @throws InvalidCastException
	 */
	public function do(string $username, string $password): void
	{
		/** @var User $adminUser */
		$adminUser = User::query()->findOrFail(0);
		$adminUser->username = $username;
		$adminUser->password = Hash::make($password);
		$adminUser->save();

		return;
	}
}
