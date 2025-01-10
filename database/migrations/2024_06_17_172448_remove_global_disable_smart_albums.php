<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const SMART_ALBUMS = 'Smart Albums';
	public const BOOL = '0|1';
	public const TABLE = 'configs';
	public const SA = 'SA_enabled';
	public const UNSORTED = 'enable_unsorted';
	public const STARRED = 'enable_starred';
	public const RECENT = 'enable_recent';
	public const ON_THIS_DAY = 'enable_on_this_day';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$val = DB::table(self::TABLE)->select('value')->where('key', '=', self::SA)->first()->value;
		DB::table(self::TABLE)->whereIn('key', [
			self::UNSORTED,
			self::STARRED,
			self::RECENT,
			self::ON_THIS_DAY, ])->update(['value' => $val]);
		DB::table(self::TABLE)->where('key', '=', self::SA)->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table(self::TABLE)->insert([
			[
				'key' => 'SA_enabled',
				'value' => '1',
				'is_secret' => false,
				'cat' => self::SMART_ALBUMS,
				'type_range' => self::BOOL,
				'description' => 'Enable Smart Albums.',
			],
		]);
	}
};
