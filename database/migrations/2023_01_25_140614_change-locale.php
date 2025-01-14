<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const NORWEGIAN = 'nb-no';
	public const CHINESE_TRADITIONAL = '繁體中文';
	public const CHINESE_SIMPLIFIED = '简体中文';

	public const NORWEGIAN_CODE = 'no';
	public const CHINESE_TRADITIONAL_CODE = 'zh_TW';
	public const CHINESE_SIMPLIFIED_CODE = 'zh_CN';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')
			->where('value', '=', self::CHINESE_TRADITIONAL)
			->update(['value' => self::CHINESE_TRADITIONAL_CODE]);

		DB::table('configs')
			->where('value', '=', self::CHINESE_SIMPLIFIED)
			->update(['value' => self::CHINESE_SIMPLIFIED_CODE]);

		DB::table('configs')
			->where('value', '=', self::NORWEGIAN)
			->update(['value' => self::NORWEGIAN_CODE]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('value', '=', self::CHINESE_TRADITIONAL_CODE)
			->update(['value' => self::CHINESE_TRADITIONAL]);

		DB::table('configs')
			->where('value', '=', self::CHINESE_SIMPLIFIED_CODE)
			->update(['value' => self::CHINESE_SIMPLIFIED]);

		DB::table('configs')
			->where('value', '=', self::NORWEGIAN_CODE)
			->update(['value' => self::NORWEGIAN]);
	}
};
