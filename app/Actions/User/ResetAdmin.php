<?php

namespace App\Actions\User;

use App\Exceptions\ModelDBException;
use App\Models\User;

/**
 * We allo to reset admin User.
 */
class ResetAdmin
{
	/**
	 * Reset admin user: set username and password to empty string ''.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function do(): void
	{
		/** @var User $user */
		$user = User::query()->findOrNew(0);
		$user->incrementing = false; // disable auto-generation of ID
		$user->id = 0;
		$user->username = '';
		$user->password = '';
		$user->save();
	}
}
