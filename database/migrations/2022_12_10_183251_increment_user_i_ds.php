<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::disableForeignKeyConstraints();
		DB::beginTransaction();

		// In the case of pgsql we mark the following foreign keys to be defered after the commit transaction
		// rather than after every requests
		if (DB::getDriverName() === 'pgsql') {
			$this->defer('base_albums', 'base_albums_owner_id_foreign');
			$this->defer('user_base_album', 'user_base_album_user_id_foreign');
			$this->defer('photos', 'photos_owner_id_foreign');
		}

		/** @var App\Models\User|null $admin */
		$admin = DB::table('users')->find(0);
		if ($admin !== null && ($admin->username === '' || $admin->password === '')) {
			// The admin user (id 0) has never set a username and password, so we remove it.
			// This should only happen on a completely new installation where the admin user is created by the
			// MigrateAdminUser migration and the user has never logged in.
			DB::table('users')->where('id', '=', 0)->delete();
		}

		/** @var App\Models\User $user */
		foreach (DB::table('users')->orderByDesc('id')->get() as $user) {
			$oldID = $user->id;
			$newID = $oldID + 1;
			DB::table('users')->where('id', '=', $oldID)->update(['id' => $newID]);
			// update other columns referencing user ID
			DB::table('base_albums')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('photos')->where('owner_id', '=', $oldID)->update(['owner_id' => $newID]);
			DB::table('user_base_album')->where('user_id', '=', $oldID)->update(['user_id' => $newID]);
			DB::table('webauthn_credentials')->where('authenticatable_id', '=', $oldID)->update(['authenticatable_id' => $newID]);
			DB::table('users')->delete($oldID);
		}

		if (DB::getDriverName() === 'pgsql' && DB::table('users')->count() > 0) {
			// when using PostgreSQL, the new IDs are not updated after incrementing. Thus, we need to reset the index to the greatest ID + 1
			// the sequence is called `users_id_seq1`
			/** @var App\Models\User $lastUser */
			$lastUser = DB::table('users')->orderByDesc('id')->first();
			DB::statement('ALTER SEQUENCE users_id_seq1 RESTART WITH ' . strval($lastUser->id + 1));
		}
		DB::commit();
		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::disableForeignKeyConstraints();
		DB::beginTransaction();

		// In the case of pgsql we mark the following foreign keys to be defered after the commit transaction
		// rather than after every requests
		if (DB::getDriverName() === 'pgsql') {
			$this->defer('base_albums', 'base_albums_owner_id_foreign');
			$this->defer('user_base_album', 'user_base_album_user_id_foreign');
			$this->defer('photos', 'photos_owner_id_foreign');
		}

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
		DB::commit();
		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Defer a foreign key evalation to the end of a transaction in pgsql.
	 */
	private function defer(string $tableName, string $fkName): void
	{
		DB::select('ALTER TABLE ' . $tableName . ' ALTER CONSTRAINT ' . $fkName . ' DEFERRABLE INITIALLY DEFERRED;');
	}
};
