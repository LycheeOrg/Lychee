<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixRotation extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('photos')->where('small', 'x')->update(['small' => '']);
		DB::table('photos')->where('small2x', 'x')->update(['small2x' => '']);
		DB::table('photos')->where('medium', 'x')->update(['medium' => '']);
		DB::table('photos')->where('medium2x', 'x')->update(['medium2x' => '']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// There is no undo
	}
}
