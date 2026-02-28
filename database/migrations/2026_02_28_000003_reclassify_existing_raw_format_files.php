<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Enum\SizeVariantType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reclassify existing files stored as ORIGINAL whose extension matches
 * `raw_formats` config (excluding .pdf) to RAW type.
 *
 * Uses `short_path` because it preserves the original file extension.
 * No JOIN to `photos` table needed.
 */
return new class() extends Migration {
	public function up(): void
	{
		$raw_formats_value = DB::table('configs')
			->where('key', '=', 'raw_formats')
			->value('value');

		if ($raw_formats_value === null || trim($raw_formats_value) === '') {
			return;
		}

		$extensions = explode('|', strtolower($raw_formats_value));
		$extensions = array_filter($extensions, fn (string $ext) => $ext !== '' && $ext !== '.pdf');

		if (count($extensions) === 0) {
			return;
		}

		// Build a query that matches ORIGINAL rows whose short_path ends with one of the raw extensions
		$query = DB::table('size_variants')
			->where('type', '=', SizeVariantType::ORIGINAL->value);

		$query->where(function ($q) use ($extensions) {
			foreach ($extensions as $ext) {
				$ext = str_starts_with($ext, '.') ? $ext : '.' . $ext;
				$q->orWhere('short_path', 'LIKE', '%' . $ext);
			}
		});

		$query->update(['type' => SizeVariantType::RAW->value]);
	}

	public function down(): void
	{
		// Reverse: set RAW rows matching raw_formats back to ORIGINAL
		DB::table('size_variants')
			->where('type', '=', SizeVariantType::RAW->value)
			->update(['type' => SizeVariantType::ORIGINAL->value]);
	}
};
