<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
			$table->text(self::NAME)->default('')->after('password')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(self::USERS, function (Blueprint $table) {
			$table->dropColumn(self::NAME);
		});
	}
}
