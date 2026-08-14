<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration {
	public function up(): void
	{
		Schema::table('landing_links', function (Blueprint $table): void {
			$table->boolean('is_built_in')->default(false)->after('enabled');
		});

		// The URL column holds a Vue Router route name (not a real URL) for
		// built-in rows; the frontend resolves it client-side. This avoids
		// baking APP_URL into stored data, which is unreliable (e.g. it may
		// change between environments or not match how the app is actually
		// accessed).
		$this->seedBuiltIn('Gallery', 'home');
		$this->seedBuiltIn('Contact', 'contact');

		// A pre-release, superseded version of this migration added a
		// `is_gallery_link` column on some local databases; clean it up if present.
		if (Schema::hasColumn('landing_links', 'is_gallery_link')) {
			Schema::table('landing_links', function (Blueprint $table): void {
				$table->dropColumn('is_gallery_link');
			});
		}
	}

	public function down(): void
	{
		DB::table('landing_links')->whereIn('url', ['home', 'contact'])->where('is_built_in', '=', true)->delete();

		Schema::table('landing_links', function (Blueprint $table): void {
			$table->dropColumn('is_built_in');
		});
	}

	/**
	 * Seeds the built-in, non-deletable row so it can be ordered and
	 * shown/hidden alongside admin-created links, same as any other row.
	 *
	 * Idempotent by `url`: if a matching row already exists (e.g. seeded by
	 * the superseded pre-release migration this one replaces), it is simply
	 * flagged as built-in instead of duplicated.
	 */
	private function seedBuiltIn(string $label, string $url): void
	{
		$existing = DB::table('landing_links')->where('url', '=', $url)->first();

		if ($existing !== null) {
			DB::table('landing_links')->where('id', '=', $existing->id)->update(['is_built_in' => true]);

			return;
		}

		$max_sort_order = DB::table('landing_links')->max('sort_order');
		$next_sort_order = $max_sort_order === null ? 0 : ((int) $max_sort_order) + 1;
		$now = now();

		DB::table('landing_links')->insert([
			'id' => (string) Str::ulid(),
			'label' => $label,
			'url' => $url,
			'placement' => 'nav',
			'open_in_new_tab' => false,
			'sort_order' => $next_sort_order,
			'enabled' => true,
			'is_built_in' => true,
			'created_at' => $now,
			'updated_at' => $now,
		]);
	}
};
