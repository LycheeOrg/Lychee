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
		Schema::create('landing_links', function (Blueprint $table): void {
			$table->ulid('id')->primary();
			$table->string('label', 255);
			$table->string('url', 2048);
			$table->string('placement', 20); // LandingLinkPlacement: nav | footer | both
			$table->boolean('open_in_new_tab')->default(true);
			$table->integer('sort_order')->default(0);
			$table->boolean('enabled')->default(true);
			$table->boolean('is_built_in')->default(false);
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);

			$table->index('enabled');
			$table->index('placement');
		});

		// The URL column holds a Vue Router route name (not a real URL) for
		// built-in rows; the frontend resolves it client-side. This avoids
		// baking APP_URL into stored data, which is unreliable (e.g. it may
		// change between environments or not match how the app is actually
		// accessed).
		$this->seedBuiltIn('Gallery', 'home');
		$this->seedBuiltIn('Contact', 'contact');
	}

	public function down(): void
	{
		Schema::dropIfExists('landing_links');
	}

	/**
	 * Seeds the built-in, non-deletable row so it can be ordered and
	 * shown/hidden alongside admin-created links, same as any other row.
	 */
	private function seedBuiltIn(string $label, string $url): void
	{
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
