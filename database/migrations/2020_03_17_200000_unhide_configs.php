<?php

/** @noinspection PhpUndefinedClassInspection */

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('key', 'SL_enable')->update(['confidentiality' => '2']);
		Configs::where('key', 'SL_for_admin')->update(['confidentiality' => '2']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'SL_enable')->update(['confidentiality' => '0']);
		Configs::where('key', 'SL_for_admin')->update(['confidentiality' => '0']);
	}
};
