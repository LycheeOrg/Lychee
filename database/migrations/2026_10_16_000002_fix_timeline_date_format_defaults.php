<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Feature 095 (FR-095-07): replaces two defaults on rows that still hold
 * them — the hour header `g:i` (no am/pm, Q-095-05) and the album day `j M`
 * (no year, Q-095-07). Customised values are left alone.
 *
 * Rolling back restores the old defaults, including on an instance whose
 * admin picked `H:00` or `j M Y` on purpose.
 *
 * The update bypasses the settings controller, so its listing flush
 * (FR-092-13) does not run: the cache is cleared here instead.
 */
return new class() extends Migration {
	/** key => [old default, new default] */
	private const DEFAULTS = [
		'timeline_photo_date_format_hour' => ['g:i', 'H:00'],
		'timeline_album_date_format_day' => ['j M', 'j M Y'],
	];

	public function up(): void
	{
		foreach (self::DEFAULTS as $key => [$old, $new]) {
			DB::table('configs')->where('key', '=', $key)->where('value', '=', $old)->update(['value' => $new]);
		}
		$this->clearCache();
	}

	public function down(): void
	{
		foreach (self::DEFAULTS as $key => [$old, $new]) {
			DB::table('configs')->where('key', '=', $key)->where('value', '=', $new)->update(['value' => $old]);
		}
		$this->clearCache();
	}

	private function clearCache(): void
	{
		try {
			Artisan::call('cache:clear');
		} catch (\Throwable) {
			// Cached listings expire on their own; the defaults are already written.
		}
	}
};
