<?php

/** @noinspection PhpUndefinedClassInspection */

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigCheckUpdateEveryCatFix extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('key', 'update_check_every_days')->update(['cat' => 'config']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'update_check_every_days')->update(['cat' => 'Config']);
	}
}
