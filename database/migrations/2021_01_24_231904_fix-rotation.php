<?php

use App\Models\Photo;
use Illuminate\Database\Migrations\Migration;

class FixRotation extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Photo::where('small', 'x')->update(['small' => '']);
		Photo::where('small2x', 'x')->update(['small2x' => '']);
		Photo::where('medium', 'x')->update(['medium' => '']);
		Photo::where('medium2x', 'x')->update(['medium2x' => '']);
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
