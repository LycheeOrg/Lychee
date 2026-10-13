<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\User;

use App\Enum\UserUploadTrustLevel;
use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Hash;

class Create
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(
		string $username,
		#[\SensitiveParameter] string $password,
		?string $email = null,
		bool $may_upload = false,
		bool $may_edit_own_settings = false,
		bool $may_administrate = false,
		?int $quota_kb = null,
		?string $note = null,
		?UserUploadTrustLevel $upload_trust_level = null,
	): User {
		$username = $this->resolveUsername($username, $email);

		if (User::query()->where('username', '=', $username)->count() !== 0) {
			throw new ConflictingPropertyException('Username already exists');
		}
		if ($email !== null && User::query()->where('email', '=', $email)->count() !== 0) {
			throw new ConflictingPropertyException('email already exists');
		}
		if ($quota_kb === 0) {
			$default_bytes = $this->config_manager->getValueAsByteSize('default_user_quota');
			$quota_kb = $default_bytes === 0 ? null : max(1, intdiv($default_bytes, 1024));
		}
		$user = new User();
		$user->may_upload = $may_upload;
		$user->may_edit_own_settings = $may_edit_own_settings;
		$user->may_administrate = $may_administrate;
		$user->username = $username;
		$user->email = $email;
		$user->password = Hash::make($password);
		$user->quota_kb = $quota_kb;
		$user->note = $note;
		$user->upload_trust_level = $upload_trust_level
			?? $this->config_manager->getValueAsEnum('default_user_trust_level', UserUploadTrustLevel::class)
			?? UserUploadTrustLevel::TRUSTED;
		$user->save();

		return $user;
	}

	/**
	 * Determine the username to store.
	 *
	 * A username given by the caller always wins. When it is omitted, the email
	 * address takes its place, which is what the `email_login_enabled` setting
	 * relies on: the account can then be identified by its email alone.
	 *
	 * @param string  $username username given by the caller, possibly empty
	 * @param ?string $email    email address of the new user
	 *
	 * @return string the username to store
	 *
	 * @throws InvalidPropertyException if neither a username nor an email is given
	 */
	private function resolveUsername(string $username, ?string $email): string
	{
		if ($username !== '') {
			return $username;
		}

		if ($email !== null && $email !== '') {
			return $email;
		}

		throw new InvalidPropertyException('Username is required when no email is given');
	}
}
