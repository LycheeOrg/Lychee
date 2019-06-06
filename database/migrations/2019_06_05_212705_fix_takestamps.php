<?php

use App\Album;
use Illuminate\Database\Migrations\Migration;

class FixTakestamps extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Album::reset_takestamp();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// There is no undo.
	}
}
