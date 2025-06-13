<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\AbstractBaseConfigMigration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends AbstractBaseConfigMigration {
	public const CAT = 'Mod Flow';
	public const NSFW = 'Mod NSFW';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => $type_range . '|flow']);

		DB::table('config_categories')->insert([
			'cat' => 'Mod Flow',
			'name' => 'Flow',
			'description' => 'Flow is a flow of albums that can be used to display the latest albums in a flow-like manner.',
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

		DB::table('config_categories')->where('cat', 'Mod Flow')->delete();

		$type_range = DB::table('configs')->select('type_range')->where('key', 'home_page_default')->first()->type_range;
		DB::table('configs')->where('key', 'home_page_default')->update(['type_range' => str_replace('|flow', '', $type_range)]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'flow_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL, // We will change the type_range later when adding for functionalities.
				'description' => 'Enable Flow display',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
				'order' => 1,
			],
			[
				'key' => 'flow_public',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL, // We will change the type_range later when adding for functionalities.
				'description' => 'Allows anonymous user to access the flow',
				'details' => '',
				'is_secret' => false,
				'level' => 0,
				'order' => 2,
			],
			[
				'key' => 'flow_base',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Base album id for the flow',
				'details' => 'All albums within this album will be included in the flow (leave empty for root).',
				'is_secret' => false,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'flow_strategy',
				'value' => 'auto',
				'cat' => self::CAT,
				'type_range' => 'auto|opt-in',
				'description' => 'Flow strategy',
				'details' => 'Choose how the flow is generated. "auto" will include all albums, "opt-in" will only include albums that have the flow enabled.',
				'is_secret' => false,
				'level' => 1,
				'order' => 4,
			],
			[
				'key' => 'flow_include_sub_albums',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Include sub albums in the flow',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
				'order' => 5,
			],
			[
				'key' => 'flow_max_items',
				'value' => '5',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Maximum number of items in the flow',
				'details' => '',
				'is_secret' => false,
				'level' => 1,
				'order' => 6,
			],
			[
				'key' => 'hide_nsfw_in_flow',
				'value' => '1',
				'cat' => self::NSFW,
				'type_range' => self::BOOL,
				'description' => 'Do not show sensitive albums in the flow',
				'details' => 'Albums marked as sensitive will not be included in the flow.',
				'is_secret' => false,
				'level' => 0,
				'order' => 32767,
			],
		];
	}
};
