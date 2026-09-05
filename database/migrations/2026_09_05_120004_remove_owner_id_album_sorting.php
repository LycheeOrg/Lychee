<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

/**
 * Feature 062 (FR-062-08, G5): `ColumnSortingAlbumType::OWNER_ID` is removed
 * as a selectable `sorting_albums_col`/`album_sorting_col` value — a UI
 * dropdown narrowing already shipped in Feature 060 quietly removed it from
 * the *offered* choices without ever removing it from the enum or migrating
 * stray existing values. Rewrites any surviving `owner_id` value (instance
 * config or per-album override) to `created_at`, mirroring
 * `2026_08_30_093447_fix_sorting_albums.php`'s exact pattern.
 *
 * `configs.type_range` for `sorting_albums_col` needs no change — Feature
 * 060 already narrowed it to `created_at|title|max_taken_at|min_taken_at`
 * (no `owner_id`).
 *
 * Deployers must re-run `lychee:recompute-album-buckets` after upgrading:
 * any row left `bucket_id=null` under a formerly-`OWNER_ID` effective column
 * now needs a real date/title value.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('albums')
			->where('album_sorting_col', '=', 'owner_id')
			->update(['album_sorting_col' => 'created_at']);

		DB::table('configs')
			->where('key', '=', 'sorting_albums_col')
			->where('value', '=', 'owner_id')
			->update(['value' => 'created_at']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// no coming back.
	}
};
