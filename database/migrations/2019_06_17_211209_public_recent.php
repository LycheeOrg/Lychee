<?php

use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class PublicRecent extends Migration
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
					'key' => 'public_recent',
					'value' => '0',
					'confidentiality' => 0,
				],
				[
					'key' => 'recent_age',
					'value' => '1',
					'confidentiality' => 2,
				],
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
			Configs::where('key', '=', 'public_recent')->delete();
			Configs::where('key', '=', 'recent_age')->delete();
		}
	}
}
