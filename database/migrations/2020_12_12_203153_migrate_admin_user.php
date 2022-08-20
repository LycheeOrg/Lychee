<?php

use App\Exceptions\ModelDBException;
use App\Models\Configs;
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
		DB::table('users')->insert([
			'id' => 0,
			'username' => Configs::getValueAsString('username', ''),
			'password' => Configs::getValueAsString('password', ''),
			'lock' => false,
			'upload' => true,
		]);
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
