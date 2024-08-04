<?php

namespace App\Actions\Profile;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

		if ($username !== null &&
			$username !== '' &&
			Configs::getValueAsBool('allow_username_change')) {
			$this->updateUsername($user, $username, $ip);
		}

		$user->password = Hash::make($password);
		$user->save();

		return $user;
	}

	/**
	 * Update Username if it does not already exists.
	 *
	 * @param User   $user
	 * @param string $username
	 * @param string $ip
	 *
	 * @return User
	 *
	 * @throws ConfigurationKeyMissingException
	 * @throws QueryBuilderException
	 * @throws ConflictingPropertyException
	 */
	public function updateUsername(User &$user, string $username, string $ip): User
	{
		// No need to change
		if ($username === $user->username) {
			return $user;
		}

		// Check if username already exists
		if (User::query()->where('username', '=', $username)->where('id', '!=', $user->id)->count() !== 0) {
			Log::channel('login')->warning(__METHOD__ . ':' . __LINE__ . sprintf('User (%s) tried to change their identity to (%s) from %s', $user->username, $username, $ip));
			throw new ConflictingPropertyException('Username already exists.');
		}

		// Change username
		if ($username !== $user->username) {
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . sprintf('User (%s) changed their identity for (%s) from %s', $user->username, $username, $ip));
			$user->username = $username;
		}

		return $user;
	}

	/**
	 * Update the email of the user.
	 *
	 * @param User    $user
	 * @param ?string $email
	 *
	 * @return User
	 */
	public function updateEmail(User &$user, ?string $email): User
	{
		$user->email = $email;
		if ($email === null) {
			$user->notifications()->delete();
		}

		return $user;
	}

	/**
	 * Update the password of the user.
	 *
	 * @param User    $user
	 * @param ?string $password
	 *
	 * @return User
	 */
	public function updatePassword(User &$user, ?string $password): User
	{
		if ($password === null) {
			return $user;
		}

		$user->password = Hash::make($password);

		return $user;
	}
}
