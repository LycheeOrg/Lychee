<?php

use App\Models\Album;
use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class FixTakedate extends Migration
{
	private const TAKEDATE = 'takedate';
	private const TAKEN_AT = 'taken_at';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('value', '=', self::TAKEDATE)->update(['value' => self::TAKEN_AT]);
		Album::where('sorting_col', '=', self::TAKEDATE)->update(['sorting_col' => self::TAKEN_AT]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('value', '=', self::TAKEN_AT)->update(['value' => self::TAKEDATE]);
		Album::where('sorting_col', '=', self::TAKEN_AT)->update(['sorting_col' => self::TAKEDATE]);
	}
}
