<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		Schema::disableForeignKeyConstraints();
		/** @var App\Models\User|null $admin */
		$admin = DB::table('users')->find(0);
		if ($admin !== null && ($admin->username === '' || $admin->password === '')) {
			// The admin user (id 0) has never set a username and password, so we remove it.
			// This should only happen on a completely new installation where the admin user is created by the
			// MigrateAdminUser migration and the user has never logged in.
			DB::table('users')->delete(0);
		}
		/** @var App\Models\User $user */
		foreach (DB::table('users')->orderByDesc('id')->get() as $user) {
			$oldID = $user->id;
			$newID = $oldID + 1;
			DB::table('users')->find($oldID)->update(['id' => $newID]);
			// update other columns referencing user ID
			DB::table('base_albums')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('photos')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('user_base_album')->where('user_id', '=', $oldID)->update(['user_id' => $newID]);
			DB::table('webauthn_credentials')->where('authenticatable_id', '=', $oldID)->update(['authenticatable_id' => $newID]);
			DB::table('users')->delete($oldID);
		}
		if (Schema::connection(null)->getConnection()->getDriverName() === 'pgsql' && DB::table('users')->count() > 0) {
			// when using PostgreSQL, the new IDs are not updated after incrementing. Thus, we need to reset the index to the greatest ID + 1
			// the sequence is called `users_id_seq1`
			/** @var App\Models\User $lastUser */
			$lastUser = DB::table('users')->orderByDesc('id')->first();
			DB::statement('ALTER SEQUENCE users_id_seq1 RESTART WITH ' . strval($lastUser->id + 1));
		}
		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::disableForeignKeyConstraints();
		/** @var App\Models\User $user */
		foreach (User::query()->orderBy('id')->get() as $user) {
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
		Schema::enableForeignKeyConstraints();
	}
};
