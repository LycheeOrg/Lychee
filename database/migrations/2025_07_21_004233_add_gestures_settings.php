<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'gestures';

	private function getConfigs(): array
	{
		return [
			[
				'key' => 'is_scroll_to_navigate_photos_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => false,
				'description' => 'Enable scrolling with mouse wheel to navigate between photos',
				'details' => '',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 1,
				'is_expert' => false,
			],
			[
				'key' => 'is_swipe_vertically_to_go_back_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => false,
				'description' => 'Enable vertical swipe gesture on photos to return to album',
				'details' => '',
				'level' => 0,
				'not_on_docker' => false,
				'order' => 2,
				'is_expert' => false,
			],
		];
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// Create the new Gestures category
		DB::table('config_categories')->insert([
			[
				'cat' => self::CAT,
				'name' => 'Gestures',
				'description' => 'Configure gesture controls for photo navigation.',
				'order' => 50,
			],
		]);

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

		// Remove the category
		DB::table('config_categories')->where('cat', self::CAT)->delete();
	}
};