<?php

use App\Models\Configs;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

class MigrateAdminUser extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$user = new User();
		$user->username = Configs::get_value('username', '');
		$user->password = Configs::get_value('password', '');
		$user->save();

		// user will have a id which is NOT 0.
		// we want this user to have an ID of 0 as it is the ADMIN ID.
		$user->id = 0;
		$user->save();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$user = User::find(0);
		$user->delete();
	}
}
