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
 * Feature 092 (FR-092-01, FR-092-02): row-intrinsic bucket keys on
 * `photos`. `taken_key` is `taken_at` in `taken_at_orig_tz` as `Y-m-d-H`
 * (ADR-092-02), indexed for the Timeline's bucket ranges; `title_date_key`
 * is the leading date of the title as `Y-m-d`.
 */
return new class() extends Migration {
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
		Schema::table('photos', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_taken_key_index');
		});

		Schema::table('photos', function (Blueprint $table) {
			if (!Schema::hasColumn('photos', 'taken_key')) {
				$table->string('taken_key', 13)->nullable()->default(null)->after('taken_at_orig_tz');
			}
			if (!Schema::hasColumn('photos', 'title_date_key')) {
				$table->string('title_date_key', 10)->nullable()->default(null)->after('title_index');
			}
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->index('taken_key', 'photos_taken_key_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_taken_key_index');
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn(['taken_key', 'title_date_key']);
		});
	}
};
