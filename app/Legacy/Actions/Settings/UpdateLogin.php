<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\Actions\Settings;

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

final class UpdateLogin
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

		if (!Hash::check($oldPassword, $user->password)) {
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . sprintf('User (%s) tried to change their identity from %s', $user->username, $ip));

			throw new UnauthenticatedException('Previous password is invalid');
		}

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
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 * @throws QueryBuilderException
	 * @throws ConflictingPropertyException
	 */
	private function updateUsername(User &$user, string $username, string $ip): void
	{
		if (User::query()->where('username', '=', $username)->where('id', '!=', $user->id)->count() !== 0) {
			Log::channel('login')->warning(__METHOD__ . ':' . __LINE__ . sprintf('User (%s) tried to change their identity to (%s) from %s', $user->username, $username, $ip));
			throw new ConflictingPropertyException('Username already exists.');
		}

		if ($username !== $user->username) {
			Log::channel('login')->notice(__METHOD__ . ':' . __LINE__ . sprintf('User (%s) changed their identity for (%s) from %s', $user->username, $username, $ip));
			$user->username = $username;
		}
	}
}
