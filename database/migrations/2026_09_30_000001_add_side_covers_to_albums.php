<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 075 (FR-075-01, ADR-075-01): ranks 2 and 3 of the automatic
 * cover selection, per privilege level, next to the existing rank-1
 * columns `auto_cover_id_max_privilege` / `auto_cover_id_least_privilege`.
 */
return new class() extends Migration {
	public const RANDOM_ID_LENGTH = 24;

	private const COLUMNS = [
		'auto_cover_id_max_privilege_2',
		'auto_cover_id_max_privilege_3',
		'auto_cover_id_least_privilege_2',
		'auto_cover_id_least_privilege_3',
	];

	public function up(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			foreach (self::COLUMNS as $column) {
				$table->char($column, self::RANDOM_ID_LENGTH)->nullable();
			}

			foreach (self::COLUMNS as $column) {
				$table->foreign($column)
					->references('id')
					->on('photos')
					->onDelete('set null');
			}
		});
	}

	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			foreach (array_reverse(self::COLUMNS) as $column) {
				$table->dropForeign([$column]);
			}

			foreach (array_reverse(self::COLUMNS) as $column) {
				$table->dropColumn($column);
			}
		});
	}
};
