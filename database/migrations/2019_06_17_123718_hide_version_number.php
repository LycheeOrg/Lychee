<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class HideVersionNumber extends Migration
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
					'key' => 'hide_version_number',
					'value' => '1',
					'confidentiality' => 3,
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
			Configs::where('key', '=', 'hide_version_number')->delete();
		}
	}
}
