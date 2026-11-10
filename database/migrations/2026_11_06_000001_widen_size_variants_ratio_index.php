<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

/**
 * Feature 108 (NFR-108-06, Q-108-05 A): widens the `(photo_id, type, ratio)`
 * index of `size_variants` with the columns Insights reads from the original
 * size variant, so they come from the index in photo-ID order instead of
 * from randomly placed table rows.
 */
return new class() extends Migration {
	private const NARROW = 'size_variants_photo_id_type_ratio_index';
	private const WIDE = 'size_variants_photo_id_type_ratio_filesize_width_height_index';

	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('size_variants', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, self::NARROW);
			$this->optimize->dropIndexIfExists($table, self::WIDE);
		});

		Schema::table('size_variants', function (Blueprint $table) {
			$table->index(['photo_id', 'type', 'ratio', 'filesize', 'width', 'height'], self::WIDE);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('size_variants', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, self::WIDE);
			$this->optimize->dropIndexIfExists($table, self::NARROW);
		});

		Schema::table('size_variants', function (Blueprint $table) {
			$table->index(['photo_id', 'type', 'ratio'], self::NARROW);
		});
	}
};
