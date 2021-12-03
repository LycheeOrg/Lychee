<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixTakedate extends Migration
{
	private const TAKESTAMP = 'takestamp';
	private const TAKEN_AT = 'taken_at';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('configs')->where('value', '=', self::TAKESTAMP)->update(['value' => self::TAKEN_AT]);
		DB::table('albums')->where('sorting_col', '=', self::TAKESTAMP)->update(['sorting_col' => self::TAKEN_AT]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('configs')->where('value', '=', self::TAKEN_AT)->update(['value' => self::TAKESTAMP]);
		DB::table('albums')->where('sorting_col', '=', self::TAKEN_AT)->update(['sorting_col' => self::TAKESTAMP]);
	}
}
