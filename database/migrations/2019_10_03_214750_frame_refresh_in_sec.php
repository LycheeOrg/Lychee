<?php

use App\Configs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class FrameRefreshInSec extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			Configs::where('key', 'Mod_Frame_refresh')
				->update(
					[
						'value' => Configs::get_value('Mod_Frame_refresh') / 1000,
					]);
		} else {
			echo "Table configs does not exist\n";
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Configs::where('key', 'Mod_Frame_refresh')
				->update(
					[
						'value' => Configs::get_value('Mod_Frame_refresh') * 1000,
					]);
		}
	}
}
