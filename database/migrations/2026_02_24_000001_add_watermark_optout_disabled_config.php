<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const MOD_WATERMARKER = 'Mod Watermarker';
	public const BOOL = '0|1';

	/**
	 * Run the migrations.
	 */
	final public function up(): void
	{
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
	}

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'watermark_optout_disabled',
				'value' => '0',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::BOOL,
				'description' => 'Disable watermark opt-out during upload',
				'details' => 'When enabled, users cannot opt-out of watermarking their uploads. All photos will be watermarked according to global settings.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 15,
			],
		];
	}
};
