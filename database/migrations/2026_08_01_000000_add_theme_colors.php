<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const CAT = 'Theme Colors';

	public function getCategory(): array
	{
		return [
			'cat' => self::CAT,
			'name' => 'Theme Colors',
			'description' => 'Customize the secondary, semantic, and neutral colors used across the v8 interface. Each picked color generates a full palette, the same way the accent color above does.',
			'order' => 7,
		];
	}

	public function getConfigs(): array
	{
		$details = 'Pick a colour, the nearest colour palette will be used. If you leave this empty, the default color will be used.<br>Note: you will need to refresh the page to see the changes.';

		return [
			[
				'key' => 'enable_design_system',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => '0|1',
				'is_secret' => true,
				'description' => 'Enable design system',
				'details' => 'Enables the design system preview in the admin panel (this is mostly a dev feature).',
				'level' => 0,
				'order' => 1,
				'is_expert' => true,
			],
			[
				'key' => 'secondary_color',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'color',
				'is_secret' => true, // It is not a secret, but we don't want to show it in diagnostics either.
				'description' => 'Secondary color',
				'details' => $details,
				'level' => 0,
				'order' => 2,
				'is_expert' => false,
			],
			[
				'key' => 'success_color',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'color',
				'is_secret' => true, // It is not a secret, but we don't want to show it in diagnostics either.
				'description' => 'Success color',
				'details' => $details,
				'level' => 0,
				'order' => 3,
				'is_expert' => false,
			],
			[
				'key' => 'warning_color',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'color',
				'is_secret' => true, // It is not a secret, but we don't want to show it in diagnostics either.
				'description' => 'Warning color',
				'details' => $details,
				'level' => 0,
				'order' => 4,
				'is_expert' => false,
			],
			[
				'key' => 'error_color',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'color',
				'is_secret' => true, // It is not a secret, but we don't want to show it in diagnostics either.
				'description' => 'Error color',
				'details' => $details,
				'level' => 0,
				'order' => 5,
				'is_expert' => false,
			],
			[
				'key' => 'info_color',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'color',
				'is_secret' => true, // It is not a secret, but we don't want to show it in diagnostics either.
				'description' => 'Info color',
				'details' => $details,
				'level' => 0,
				'order' => 6,
				'is_expert' => false,
			],
			[
				'key' => 'neutral_color',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => 'color',
				'is_secret' => true, // It is not a secret, but we don't want to show it in diagnostics either.
				'description' => 'Neutral color',
				'details' => $details . ' Pick a near-gray color for best results — a saturated color will tint every gray in the interface.',
				'level' => 0,
				'order' => 7,
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
		DB::table('configs')->where('key', 'accent_color')->update(['key' => 'primary_color']);
		DB::table('config_categories')->insert($this->getCategory());
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
		DB::table('config_categories')->where('cat', self::CAT)->delete();
	}
};
