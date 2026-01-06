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
	private const RANDOM_ID_LENGTH = 24;
	private const PHOTO_ID = 'photo_id';

	/**
	 * Run the migrations.
	 *
	 * This migration preserves size variants that are referenced by order items
	 * even when the parent photo is deleted. This ensures customers can still
	 * download their purchased content after photo deletion.
	 *
	 * Changes:
	 * 1. Makes photo_id nullable in size_variants table
	 * 2. Updates foreign key to use onDelete('set null')
	 */
	public function up(): void
	{
		// Step 1: Drop the existing foreign key constraint on size_variants.photo_id
		try {
			Schema::table('size_variants', function (Blueprint $table) {
				$table->dropForeign(['photo_id']);
			});
		} catch (\Exception $e) {
			// If the foreign key does not exist, we can safely ignore the error
		}

		// Step 2: Make photo_id nullable in size_variants
		Schema::table('size_variants', function (Blueprint $table) {
			$table->char(self::PHOTO_ID, self::RANDOM_ID_LENGTH)->nullable(true)->change();
		});

		// Step 3: Re-add the foreign key with onDelete('set null')
		Schema::table('size_variants', function (Blueprint $table) {
			$table->foreign(self::PHOTO_ID)
				->references('id')
				->on('photos')
				->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * This restores the original constraints where photo_id was required
	 * and deletion was not handled gracefully.
	 */
	public function down(): void
	{
		// Reverse size_variants foreign key changes
		Schema::table('size_variants', function (Blueprint $table) {
			$table->dropForeign(['photo_id']);
		});

		// Make photo_id non-nullable again
		Schema::table('size_variants', function (Blueprint $table) {
			$table->char(self::PHOTO_ID, self::RANDOM_ID_LENGTH)->nullable(false)->change();
		});

		// Re-add original foreign key without onDelete
		Schema::table('size_variants', function (Blueprint $table) {
			$table->foreign(self::PHOTO_ID)
				->references('id')
				->on('photos');
		});
	}
};
