<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use App\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConfigHasFFmpeg extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');
		defined('TERNARY') or define('TERNARY', '0|1|2');

		// Let's run the check for ffmpeg right here
		$has_ffmpeg = 2; // not set
		try {
			$path = exec('command -v ffmpeg');
			if ($path == '') {
				$has_ffmpeg = 0; // false
			} else {
				$has_ffmpeg = 1; // true
			}
		} catch (\Exception $e) {
			$has_ffmpeg = 0;
			// let's do nothing
		}

		if (Schema::hasTable('configs')) {
			DB::table('configs')->insert([
				[
					'key' => 'has_ffmpeg',
					'value' => $has_ffmpeg,
					'confidentiality' => 2,
					'cat' => 'Image Processing',
					'type_range' => TERNARY,
				],
			]);
		} else {
			Logs::warning(__METHOD__, __LINE__, 'Table configs does not exist');
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
			Configs::where('key', '=', 'has_ffmpeg')->delete();
		}
	}
}
