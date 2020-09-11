<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class ConfigMapMod extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('key', '=', 'map_display')->update(['cat' => 'Mod Map']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', '=', 'map_display')->update(['cat' => 'config']);
	}
}
