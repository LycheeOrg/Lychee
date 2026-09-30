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
 * Feature 075 (FR-075-08, ADR-075-01): ranks 2 and 3 of the cached
 * per-viewer cover of tag/person/smart albums. `photo_id` keeps its
 * cascade (an emptied primary still removes the row); the side columns
 * are nulled instead so the row survives with its primary.
 */
return new class() extends Migration {
	public const RANDOM_ID_LENGTH = 24;

	private const TABLE_NAME = 'album_user_thumbs';

	private const COLUMNS = ['photo_id_2', 'photo_id_3'];

	public function up(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
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
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			foreach (array_reverse(self::COLUMNS) as $column) {
				$table->dropForeign([$column]);
			}

			foreach (array_reverse(self::COLUMNS) as $column) {
				$table->dropColumn($column);
			}
		});
	}
};
