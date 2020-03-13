<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
use App\Logs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ConfigExiftoolTernary extends Migration
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

		// Let's run the check for exiftool right here
		$has_exiftool = 2; // not set
		try {
			$path = exec('command -v exiftool');
			if ($path == '') {
				$has_exiftool = 0; // false
			} else {
				$has_exiftool = 1; // true
			}
		} catch (\Exception $e) {
			// let's do nothing
		}

		if (Schema::hasTable('configs')) {
			Configs::where('key', '=', 'has_exiftool')
			  ->update(
				[
					'value' => $has_exiftool,
					'type_range' => TERNARY,
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
		defined('BOOL') or define('BOOL', '0|1');

		if (env('DB_DROP_CLEAR_TABLES_ON_ROLLBACK', false)) {
			Configs::where('key', '=', 'has_exiftool')
				->update(
				[
					'value' => null,
					'type_range' => BOOL,
				]);
		}
	}
}
