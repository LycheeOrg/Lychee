<?php

namespace App\Actions\Settings;

use App\Exceptions\InvalidPropertyException;
use App\Models\User;
use Hash;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class SetLogin
{
	/**
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws BindingResolutionException
	 * @throws ModelNotFoundException
	 * @throws InvalidArgumentException
	 * @throws InvalidCastException
	 */
	public function do(string $username, string $password): void
	{
		$adminUser = User::query()->findOrFail(0);
		$adminUser->username = $username;
		$adminUser->password = Hash::make($password);
		$adminUser->save();

		return;
	}
}
