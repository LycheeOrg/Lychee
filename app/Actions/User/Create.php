<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\ModelDBException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Create
{
	/**
	 * @throws InvalidPropertyException
	 * @throws ModelDBException
	 */
	public function do(string $username, string $password, bool $mayUpload, bool $mayEditOwnSettings): User
	{
		if (User::query()->where('username', '=', $username)->count() !== 0) {
			throw new ConflictingPropertyException('Username already exists');
		}
		$user = new User();
		$user->may_upload = $mayUpload;
		$user->may_edit_own_settings = $mayEditOwnSettings;
		$user->may_administrate = false;
		$user->username = $username;
		$user->password = Hash::make($password);
		$user->save();

		return $user;
	}
}
