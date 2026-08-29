<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Feature 060 (FR-060-10): `title_strict`, `description`, and
 * `description_strict` are no longer valid sort criteria (Title/Title-strict
 * collapsed into a single DB-driven Title order; Description is no longer a
 * sort criterion at all). Existing `sorting_photos_col`/`sorting_albums_col`/
 * `sorting_pinned_albums_col` config rows are rewritten to `title`, and their
 * `type_range` is narrowed accordingly. Idempotent: re-running finds no rows
 * with the removed values left to rewrite (S-060-07/08).
 */
return new class() extends Migration {
	private const REMOVED_VALUES = ['title_strict', 'description', 'description_strict'];

	private const KEYS = ['sorting_photos_col', 'sorting_albums_col', 'sorting_pinned_albums_col'];

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		foreach (self::KEYS as $key) {
			DB::table('configs')
				->where('key', '=', $key)
				->whereIn('value', self::REMOVED_VALUES)
				->update(['value' => 'title']);
		}

		DB::table('configs')
			->where('key', '=', 'sorting_photos_col')
			->update(['type_range' => 'created_at|taken_at|title|is_highlighted|type']);

		DB::table('configs')
			->where('key', '=', 'sorting_albums_col')
			->update(['type_range' => 'created_at|title|max_taken_at|min_taken_at']);

		DB::table('configs')
			->where('key', '=', 'sorting_pinned_albums_col')
			->update(['type_range' => 'created_at|title|max_taken_at|min_taken_at']);

		DB::table('config_categories')->where('cat', 'Gallery')->update(['description' => '']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', 'sorting_photos_col')
			->update(['type_range' => 'created_at|taken_at|title|description|is_highlighted|type|title_strict|description_strict']);

		DB::table('configs')
			->where('key', '=', 'sorting_albums_col')
			->update(['type_range' => 'created_at|title|description|max_taken_at|min_taken_at|title_strict|description_strict']);

		DB::table('configs')
			->where('key', '=', 'sorting_pinned_albums_col')
			->update(['type_range' => 'created_at|title|description|max_taken_at|min_taken_at|title_strict|description_strict']);

		DB::table('config_categories')->where('cat', 'Gallery')->update(['description' => '<span class="text-warning-600 font-bold uppercase">Important:</span> Natural sorting and lexicographical sorting can produce different results.<br><span class="text-muted-color text-muted">Natural: img_1, img_2, img_10.<br>Lexicographical: img_1, img_10, img_2.<br>Lexicographical sorting is performed directly in the database, while natural sorting is performed in PHP after the database query runs. As a result, when natural sorting is combined with pagination, results are fetched from the database in an arbitrary order before being sorted, which can lead to unexpected results when browsing through pages. We recommend using natural sorting for a small number of photos, and lexicographical sorting for a large number of photos. Alternatively, you can prefix your numbers with 0s (e.g. img_01, img_02, img_10) so that both sorting methods produce the same result.</span>']);
	}
};
