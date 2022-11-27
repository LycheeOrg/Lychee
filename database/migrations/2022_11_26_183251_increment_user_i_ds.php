<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IncrementUserIDs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::disableForeignKeyConstraints();
		}
		/** @var App\Models\User $user */
		$user = DB::table('users')->find(0);
		if ($user !== null && ($user->username === '' || $user->password === '')) {
			// The admin user (id 0) has never set a username and password, so we remove it.
			// This should only happen on a completely new installation where the admin user is created by the
			// MigrateAdminUser migration and the user has never logged in.
			DB::table('users')->delete(0);
		}
		foreach (User::query()->orderByDesc('id')->get() as $user) {
			$oldID = $user->id;
			$newID = $oldID + 1;
			$user->id = $newID;
			$user->incrementing = false;
			$user->save();
			// update other columns referencing user ID
			DB::table('base_albums')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('photos')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('user_base_album')->where('user_id', '=', $oldID)->update(['user_id' => $newID]);
			DB::table('webauthn_credentials')->where('authenticatable_id', '=', $oldID)->update(['authenticatable_id' => $newID]);
			DB::table('users')->delete($oldID);
		}
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::enableForeignKeyConstraints();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::disableForeignKeyConstraints();
		}
		/** @var App\Models\User $user */
		foreach (DB::table('users')->get() as $user) {
			$oldID = $user->id;
			$newID = $oldID - 1;
			$user->id = $newID;
			$user->incrementing = false;
			$user->save();
			// update other columns referencing user ID
			DB::table('base_albums')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('photos')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('user_base_album')->where('user_id', '=', $oldID)->update(['user_id' => $newID]);
			DB::table('webauthn_credentials')->where('authenticatable_id', '=', $oldID)->update(['authenticatable_id' => $newID]);
			DB::table('users')->delete($oldID);
		}
		if (Schema::connection(null)->getConnection()->getDriverName() === 'sqlite') {
			Schema::enableForeignKeyConstraints();
		}
	}
}
