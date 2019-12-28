<?php

/** @noinspection PhpUndefinedClassInspection */
use App\Configs;
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
		if (!defined('BOOL')) {
			define('BOOL', '0|1');
		}
		if (!defined('TERNARY')) {
			define('TERNARY', '0|1|2');
		}

		// Let's run the check for exiftool right here
		$status = 0;
		$output = '';
		$has_exiftool = 2; // not set
		try {
			exec('which exiftool 2>&1 > /dev/null', $output, $status);
			if ($status != 0) {
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
		if (!defined('BOOL')) {
			define('BOOL', '0|1');
		}
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
