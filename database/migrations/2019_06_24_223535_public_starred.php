<?php

use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class PublicStarred extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'public_starred',
					'value' => '0',
					'confidentiality' => 0,
				],
			]);
		} else {
			echo "Table configs does not exists\n";
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
			Configs::where('key', '=', 'public_starred')->delete();
		}
	}
}
