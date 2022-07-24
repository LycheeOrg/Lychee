<?php

namespace App\Actions\Settings;

use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UpdateLogin
{
	/**
	 * Changes and modifies login parameters of CURRENT user (may be admin).
	 *
	 * @param string|null $username
	 * @param string      $password
	 * @param string      $oldPassword
	 * @param string      $ip
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 * @throws AuthenticationException
	 * @throws UnauthenticatedException
	 * @throws QueryBuilderException
	 * @throws ConflictingPropertyException
	 * @throws ModelDBException
	 */
	public function do(?string $username, string $password, string $oldPassword, string $ip): void
	{
		/** @var User $user */
		$user = Auth::authenticate();

		if (!Hash::check($oldPassword, $user->password)) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change their identity from ' . $ip);

			throw new UnauthenticatedException('Previous password is invalid');
		}

		if (User::query()->where('username', '=', $username)->where('id', '!=', $user->id)->count() !== 0) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change their identity to ' . $username . ' from ' . $ip);
			throw new ConflictingPropertyException('Username already exists.');
		}

		if ($username !== null && $username !== $user->username) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') changed their identity for (' . $username . ') from ' . $ip);
			$user->username = $username;
		}

		$user->password = Hash::make($password);
		$user->save();

		return;
	}
}
