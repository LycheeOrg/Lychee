<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFullNameCol extends Migration
{
	private const USERS = 'users';
	private const NAME = 'fullname';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(self::USERS, function (Blueprint $table) {
			$table->string(self::NAME, 128)->after('password')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$dbc = 'database.connections.' . Config::get('database.default');
		$database = Config::get($dbc);
		if ($database['driver'] == 'sqlite') {
			$fc = $database['foreign_key_constraints'];
			DB::statement('PRAGMA foreign_keys = OFF');
		}
		Schema::table(self::USERS, function (Blueprint $table) {
			$table->dropColumn(self::NAME);
		});
		if (($database['driver'] == 'sqlite') && ($fc != 0)) {
			DB::statement('PRAGMA foreign_keys = ON');
		}
	}
}
