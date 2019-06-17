<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class GenDemoJs extends Migration
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
					'key' => 'gen_demo_js',
					'value' => '0',
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
			Configs::where('key', '=', 'gen_demo_js')->delete();
		}
	}
}
