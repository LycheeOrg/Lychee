<?php

namespace Tests\Feature\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * This trait allows BasePhotoTests to be executed as user with Upload Rights directly.
 */
trait ExecuteAsMayUploadUser
{
	/**
	 * We create a new user with ID 2 and upload rights.
	 *
	 * @return int
	 */
	protected function executeAs(): int
	{
		/** @var User|null $user */
		$user = User::find(2);
		if ($user === null) {
			$user = new User();
			$user->incrementing = false;
			$user->id = 2;
			$user->may_upload = true;
			$user->may_edit_own_settings = false;
			$user->may_administrate = false;
			$user->username = 'NOT admin';
			$user->password = Hash::make('password');
			$user->save();

			if (Schema::connection(null)->getConnection()->getDriverName() === 'pgsql' && DB::table('users')->count() > 0) {
				// when using PostgreSQL, the next ID value is kept when inserting without incrementing
				// which results in errors because trying to insert a user with ID = 1.
				// Thus, we need to reset the index to the greatest ID + 1
				/** @var User $lastUser */
				$lastUser = User::query()->orderByDesc('id')->first();
				DB::statement('ALTER SEQUENCE users_id_seq1 RESTART WITH ' . strval($lastUser->id + 1));
			}
		} elseif (!$user->may_upload) {
			$user->may_upload = true;
			$user->save();
		}

		return 2;
	}

	/**
	 * Delete User created.
	 *
	 * @return void
	 */
	protected function logoutAs(): void
	{
		// RIP
		User::where('id', '=', 2)->delete();
	}
}