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
					'type_range' => self::TERNARY,
				]
			);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'has_exiftool')
			->update(
				[
					'value' => null,
					'type_range' => self::BOOL,
				]
			);
	}
};
