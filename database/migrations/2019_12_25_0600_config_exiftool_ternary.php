<?php

/** @noinspection PhpUndefinedClassInspection */

use App\Models\Configs;
use Illuminate\Database\Migrations\Migration;

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

		Configs::where('key', '=', 'has_exiftool')
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

		Configs::where('key', '=', 'has_exiftool')
			->update(
				[
					'value' => null,
					'type_range' => BOOL,
				]
			);
	}
}
