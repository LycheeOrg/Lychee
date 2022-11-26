<?php

use App\Models\BaseAlbumImpl;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		/** @var User $user */
		$user = User::find(0);
		if ($user !== null && ($user->username === '' || $user->password === '')) {
			// The admin user (id 0) has never set a username and password, so we remove it.
			// This should only happen on a completely new installation where the admin user is created by the
			// MigrateAdminUser migration and the user has never logged in.
			$user->delete();
		}
		foreach (User::query()->orderByDesc('id')->get() as $user) {
			$oldID = $user->id;
			$newID = $oldID + 1;
			// update other columns referencing user ID
			BaseAlbumImpl::query()->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			Photo::query()->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('user_base_album')->where('user_id', '=', $oldID)->update(['user_id' => $newID]);
			DB::table('webauthn_credentials')->where('authenticatable_id', '=', $oldID)->update(['authenticatable_id' => $newID]);
			$user->id = $newID;
			$user->incrementing = false;
			$user->save();
			User::query()->where('id', '=', $oldID)->delete();
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		/** @var User $user */
		foreach (User::all() as $user) {
			$oldID = $user->id;
			$newID = $oldID - 1;
			// update other columns referencing user ID
			BaseAlbumImpl::query()->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			Photo::query()->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('user_base_album')->where('user_id', '=', $oldID)->update(['user_id' => $newID]);
			DB::table('webauthn_credentials')->where('authenticatable_id', '=', $oldID)->update(['authenticatable_id' => $newID]);
			$user->id = $newID;
			$user->incrementing = false;
			$user->save();
			User::query()->where('id', '=', $oldID)->delete();
		}
	}
};
