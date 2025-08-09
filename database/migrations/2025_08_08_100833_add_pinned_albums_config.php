<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'Smart Albums';

	private function getConfigs(): array
	{
		return [
			[
				'key' => 'sorting_pinned_albums_col',
				'value' => 'created_at',
				'cat' => self::CAT,
				'type_range' => 'created_at|title|description|max_taken_at|min_taken_at',
				'is_secret' => false,
				'description' => 'Default column used for sorting featured albums',
				'details' => '',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 20,
				'is_expert' => false,
			],
			[
				'key' => 'sorting_pinned_albums_order',
				'value' => 'DESC',
				'cat' => self::CAT,
				'type_range' => 'ASC|DESC',
				'is_secret' => false,
				'description' => 'Default order used for sorting featured albums',
				'details' => '',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 21,
				'is_expert' => false,
			],
		];
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// Rename the category
		DB::table('config_categories')->where('cat', '=', 'Smart Albums')->update(['name' => 'Smart & Featured Albums']);

		// Add the two gesture settings
		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Remove the settings
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();

		// Rename the category
		DB::table('config_categories')->where('cat', '=', 'Smart Albums')->update(['name' => 'Smart Albums']);
	}
};