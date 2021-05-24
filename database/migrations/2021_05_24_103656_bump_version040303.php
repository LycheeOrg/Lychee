<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class BumpVersion040303 extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('key', 'version')->update(['value' => '040303']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'version')->update(['value' => '040302']);
	}
}
