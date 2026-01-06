<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// Also add columns to albums table for Album model
		Schema::table('albums', function (Blueprint $table) {
			// Date range fields (nullable - empty albums have NULL)
			$table->dateTime('max_taken_at')->nullable();
			$table->dateTime('min_taken_at')->nullable();

			// Count fields (default 0 for empty albums)
			$table->integer('num_children')->default(0);
			$table->integer('num_photos')->default(0);

			// Automatic cover IDs with foreign key constraints
			$table->char('auto_cover_id_max_privilege', self::RANDOM_ID_LENGTH)->nullable();
			$table->char('auto_cover_id_least_privilege', self::RANDOM_ID_LENGTH)->nullable();

			// Foreign key constraints with ON DELETE SET NULL
			$table->foreign('auto_cover_id_max_privilege')
				->references('id')
				->on('photos')
				->onDelete('set null');

			$table->foreign('auto_cover_id_least_privilege')
				->references('id')
				->on('photos')
				->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			// Drop foreign keys first
			$table->dropForeign(['auto_cover_id_least_privilege']);
			$table->dropForeign(['auto_cover_id_max_privilege']);

			// Drop columns in reverse order
			$table->dropColumn('auto_cover_id_least_privilege');
			$table->dropColumn('auto_cover_id_max_privilege');
			$table->dropColumn('num_photos');
			$table->dropColumn('num_children');
			$table->dropColumn('min_taken_at');
			$table->dropColumn('max_taken_at');
		});
	}
};
