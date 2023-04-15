<?php

/** @noinspection PhpUndefinedClassInspection */

use App\Facades\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use function Safe\exec;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		defined('BOOL') or define('BOOL', '0|1');
		defined('TERNARY') or define('TERNARY', '0|1|2');

		if (Helpers::isExecAvailable()) {
			// Let's run the check for ffmpeg right here
			$has_ffmpeg = 2; // not set
			try {
				$path = exec('command -v ffmpeg');
				if ($path === '') {
					$has_ffmpeg = 0; // false
				} else {
					$has_ffmpeg = 1; // true
				}
			} catch (\Exception $e) {
				$has_ffmpeg = 0;
				// let's do nothing
			}
		} else {
			$has_ffmpeg = 0; // we cannot use it anyway because exec is not available
		}

		DB::table('configs')->insert([
			[
				'key' => 'has_ffmpeg',
				'value' => $has_ffmpeg,
				'confidentiality' => 2,
				'cat' => 'Image Processing',
				'type_range' => TERNARY,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'has_ffmpeg')->delete();
	}
};
