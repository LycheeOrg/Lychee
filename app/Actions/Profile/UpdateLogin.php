<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Profile;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UpdateLogin
{
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
		if (User::query()->where('username', '=', $username)->count() !== 0) {
			Log::channel('login')->warning(__METHOD__ . ':' . __LINE__ . sprintf('User (%s) tried to change their identity to (%s) from %s', $user->username, $username, $ip));
			throw new ConflictingPropertyException('Username already exists.');
		}

		// Change username
		Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . sprintf('User (%s) changed their identity to (%s) from %s', $user->username, $username, $ip));
		$user->username = $username;

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
