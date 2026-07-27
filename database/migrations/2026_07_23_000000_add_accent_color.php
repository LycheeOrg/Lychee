<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'config';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'accent_color',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'color',
				'is_secret' => false,
				'description' => 'Accent color',
				'details' => 'Pick a colour, the nearest colour palette will be used. If you leave this empty, the default accent color will be used.<br>Note: you will need to refresh the page to see the changes.',
				'level' => 0,
				'order' => 1,
				'is_expert' => false,
			],
		];
	}

	/**
	 * Run the migrations.
	 *
	 *  @codeCoverageIgnore Tested but before CI run...
	 */
	final public function up(): void
	{
		DB::table('configs')->where('order', '>', '0')->where('order', '<', '1000')->where('cat', self::CAT)->increment('order', 2);
		DB::table('configs')->where('key', 'use_admin_dashboard')->update(['order' => '2']);
		DB::table('configs')->insert($this->getConfigs());
	}

	/**
	 * Reverse the migrations.
	 *
	 * @codeCoverageIgnore Tested but after CI run...
	 */
	final public function down(): void
	{
		$keys = collect($this->getConfigs())->map(fn ($v) => $v['key'])->all();
		DB::table('configs')->whereIn('key', $keys)->delete();
		DB::table('configs')->where('order', '>', '0')->where('order', '<', '1000')->where('cat', self::CAT)->decrement('order', 2);
	}
};
