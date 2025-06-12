<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\AbstractBaseConfigMigration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends AbstractBaseConfigMigration {
	public const CAT = 'Mod Feed';
	public const NSFW = 'Mod NSFW';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => $type_range . '|feed']);

		DB::table('config_categories')->insert([
			'cat' => 'Mod Feed',
			'name' => 'Feed',
			'description' => '',
			'order' => 21,
		]);

		DB::table('configs')->insert($this->getConfigs());

		Schema::table('base_albums', function (Blueprint $table) {
			$table->dateTime('published_at', 0)->nullable(true)->after('updated_at')->index();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('base_albums', function (Blueprint $table) {
			$table->dropIndex(['published_at']);
			$table->dropColumn('published_at');
		});

		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();

		DB::table('config_categories')->where('cat', 'Mod Feed')->delete();

		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => str_replace('|feed', '', $type_range)]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'feed_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL, // We will change the type_range later when adding for functionalities.
				'description' => 'Enable Feed display',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
				'order' => 1,
			],
			[
				'key' => 'feed_public',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL, // We will change the type_range later when adding for functionalities.
				'description' => 'Allows anonymous user to access the feed',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
				'order' => 2,
			],
			[
				'key' => 'feed_base',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Base album id for the feed',
				'details' => 'All albums within this album will be included in the feed (leave empty for root).',
				'is_secret' => false,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'feed_strategy',
				'value' => 'auto',
				'cat' => self::CAT,
				'type_range' => 'auto|opt-in',
				'description' => 'Feed strategy',
				'details' => 'Choose how the feed is generated. "auto" will include all albums, "opt-in" will only include albums that have the feed enabled.',
				'is_secret' => false,
				'level' => 1,
				'order' => 4,
			],
			[
				'key' => 'feed_include_sub_albums',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Include sub albums in the feed',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
				'order' => 5,
			],
			[
				'key' => 'feed_max_items',
				'value' => '5',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Maximum number of items in the feed',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
				'order' => 6,
			],
			[
				'key' => 'hide_nsfw_in_feed',
				'value' => '1',
				'cat' => self::NSFW,
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive albums in the feed',
				'details' => 'Albums marked as sensitive will not be included in the feed.',
				'is_secret' => false,
				'level' => 0,
				'order' => 32767,
			],
		];
	}
};
