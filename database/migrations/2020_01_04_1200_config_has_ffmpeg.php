<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use Illuminate\Database\Migrations\Migration;
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
		if (!defined('BOOL')) {
			define('BOOL', '0|1');
		}
		if (!defined('TERNARY')) {
			define('TERNARY', '0|1|2');
		}

		// Let's run the check for exiftool right here
		$status = 0;
		$output = '';
		$has_ffmpeg = 2; // not set
		try {
			exec('which ffmpeg 2>&1 > /dev/null', $output, $status);
			if ($status != 0) {
				$has_ffmpeg = 0; // false
			} else {
				$has_ffmpeg = 1; // true
			}
		} catch (\Exception $e) {
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
			Configs::where('key', '=', 'has_ffmpeg')->delete();
		}
	}
}
