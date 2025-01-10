<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const SQUARE = 'square';
	public const JUSTIFIED = 'justified';
	public const UNJUSTIFIED = 'unjustified'; // ! Legacy
	public const MASONRY = 'masonry';
	public const GRID = 'grid';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'layout')->update([
			'type_range' => self::SQUARE . '|' . self::JUSTIFIED . '|' . self::MASONRY . '|' . self::GRID,
			'description' => 'Layout for pictures',
		],
		);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'layout')->update([
			'type_range' => self::SQUARE . '|' . self::JUSTIFIED . '|' . self::UNJUSTIFIED,
			'description' => 'Layout for pictures',
		],
		);
	}
};
