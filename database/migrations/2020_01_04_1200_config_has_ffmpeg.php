<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Facades\Helpers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use function Safe\exec;

return new class() extends Migration {
	public const BOOL = '0|1';
	public const TERNARY = '0|1|2';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
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
				'type_range' => self::TERNARY,
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
