<?php

/** @noinspection PhpUndefinedClassInspection */

use App\Facades\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use function Safe\exec;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		defined('BOOL') or define('BOOL', '0|1');
		defined('TERNARY') or define('TERNARY', '0|1|2');

		if (Helpers::isExecAvailable()) {
			// Let's run the check for exiftool right here
			$has_exiftool = 2; // not set
			try {
				$path = exec('command -v exiftool');
				if ($path === '') {
					$has_exiftool = 0; // false
				} else {
					$has_exiftool = 1; // true
				}
			} catch (\Exception $e) {
				// let's do nothing
			}
		} else {
			$has_exiftool = 0; // we cannot use it anyway.
		}

		DB::table('configs')->where('key', '=', 'has_exiftool')
			->update(
				[
					'value' => $has_exiftool,
					'type_range' => TERNARY,
				]
			);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		defined('BOOL') or define('BOOL', '0|1');

		DB::table('configs')->where('key', '=', 'has_exiftool')
			->update(
				[
					'value' => null,
					'type_range' => BOOL,
				]
			);
	}
};
