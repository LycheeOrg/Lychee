<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\User;

use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Save
{
	/**
	 * @param User        $user
	 * @param string      $username
	 * @param string|null $password           see {@link HasPasswordTrait::password()} for the difference between the values `''` and `null`
	 * @param bool        $mayUpload
	 * @param bool        $mayEditOwnSettings
	 *
	 * @return void
	 *
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(User $user,
		string $username,
		?string $password,
		bool $mayUpload,
		bool $mayEditOwnSettings,
		?int $quota_kb = null,
		?string $note = null,
	): void {
		if (User::query()
			->where('username', '=', $username)
			->where('id', '!=', $user->id)
			->count() !== 0
		) {
			throw new ConflictingPropertyException('Username already exists');
		}

		if ($quota_kb === 0) {
			$default = \Configs::getValueAsInt('default_user_quota');
			$quota_kb = $default === 0 ? null : $default;
		}

		$user->username = $username;
		$user->may_upload = $mayUpload;
		$user->may_edit_own_settings = $mayEditOwnSettings;
		$user->note = $note;
		$user->quota_kb = $quota_kb;
		if ($password !== null && $password !== '') {
			$user->password = Hash::make($password);
		}
		$user->save();
	}
}
