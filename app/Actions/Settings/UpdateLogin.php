<?php

namespace App\Actions\Settings;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
	 * @return User updated user
	 *
	 * @throws UnauthenticatedException
	 * @throws QueryBuilderException
	 * @throws ConflictingPropertyException
	 * @throws ModelDBException
	 */
	public function do(?string $username, string $password, string $oldPassword, string $ip): User
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthenticatedException();

		$this->updatePassword($user, $password, $oldPassword, $ip);

		if ($username !== null && Configs::getValueAsBool('allow_username_change')) {
			$this->updateUsername($user, $username, $ip);
		}

		$user->save();

		return $user;
	}

	/**
	 * Update Password if old password is correct.
	 *
	 * @param User   $user
	 * @param string $password
	 * @param string $oldPassword
	 * @param string $ip
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 * @throws UnauthenticatedException
	 */
	private function updatePassword(User &$user, string $password, string $oldPassword, string $ip)
	{
		if (!Hash::check($oldPassword, $user->password)) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change their identity from ' . $ip);

			throw new UnauthenticatedException('Previous password is invalid');
		}

		$user->password = Hash::make($password);
	}

	/**
	 * Update Username if it does not already exists.
	 *
	 * @param User        $user
	 * @param string|null $username
	 * @param string      $ip
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 * @throws QueryBuilderException
	 * @throws ConflictingPropertyException
	 */
	private function updateUsername(User &$user, string $username, string $ip): void
	{
		if (User::query()->where('username', '=', $username)->where('id', '!=', $user->id)->count() !== 0) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') tried to change their identity to ' . $username . ' from ' . $ip);
			throw new ConflictingPropertyException('Username already exists.');
		}

		if ($username !== $user->username) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $user->username . ') changed their identity for (' . $username . ') from ' . $ip);
			$user->username = $username;
		}
	}
}
