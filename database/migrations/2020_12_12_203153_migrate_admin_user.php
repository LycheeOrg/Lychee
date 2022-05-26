<?php

use App\Exceptions\ModelDBException;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAdminUser extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function up(): void
	{
		$user = new User();
		$user->username = Configs::get_value('username', '');
		$user->password = Configs::get_value('password', '');
		$user->save();

		// User will have an ID which is NOT 0.
		// We want this user to have an ID of 0 as it is the ADMIN ID.
		$user->id = 0;
		$user->save();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		if (Schema::hasTable('users')) {
			DB::table('users')
				->where('id', '=', 0)
				->delete();
		}
	}
}
