<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class PhotosWraparound extends Migration
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
					'key' => 'photos_wraparound',
					'value' => '1',
					'confidentiality' => 0,
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
			Configs::where('key', '=', 'photos_wraparound')->delete();
		}
	}
}
