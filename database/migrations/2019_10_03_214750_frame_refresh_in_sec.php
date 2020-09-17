<?php

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

class FrameRefreshInSec extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Configs::where('key', 'Mod_Frame_refresh')
			->update(
				[
					'value' => Configs::get_value('Mod_Frame_refresh') / 1000,
				]
			);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Configs::where('key', 'Mod_Frame_refresh')
			->update(
				[
					'value' => Configs::get_value('Mod_Frame_refresh') * 1000,
				]
			);
	}
}
