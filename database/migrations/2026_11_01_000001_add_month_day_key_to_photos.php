<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

/**
 * Feature 106 (FR-106-01, FR-106-04): `photos.month_day_key`, month × 100 +
 * day of `taken_key` (the local day where the photo was taken), else of
 * `created_at`, indexed for the On This Day smart album. Filled in two
 * statements, only on rows without a key.
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
			$this->optimize->dropIndexIfExists($table, 'photos_month_day_key_index');
		});

		Schema::table('photos', function (Blueprint $table) {
			if (!Schema::hasColumn('photos', 'month_day_key')) {
				$table->unsignedSmallInteger('month_day_key')->nullable()->default(null)->after('taken_key');
			}
		});

		[$from_taken_key, $from_created_at] = match (DB::getDriverName()) {
			'sqlite' => [
				'CAST(SUBSTR(taken_key, 6, 2) || SUBSTR(taken_key, 9, 2) AS INTEGER)',
				"CAST(strftime('%m%d', created_at) AS INTEGER)",
			],
			'pgsql' => [
				'CAST(SUBSTR(taken_key, 6, 2) || SUBSTR(taken_key, 9, 2) AS INTEGER)',
				"CAST(to_char(created_at, 'MMDD') AS INTEGER)",
			],
			'mysql', 'mariadb' => [
				'CAST(CONCAT(SUBSTR(taken_key, 6, 2), SUBSTR(taken_key, 9, 2)) AS UNSIGNED)',
				"CAST(DATE_FORMAT(created_at, '%m%d') AS UNSIGNED)",
			],
			default => throw new \RuntimeException('Unsupported database driver'),
		};

		DB::table('photos')
			->whereNull('month_day_key')
			->whereNotNull('taken_key')
			->update(['month_day_key' => DB::raw($from_taken_key)]);

		DB::table('photos')
			->whereNull('month_day_key')
			->whereNull('taken_key')
			->update(['month_day_key' => DB::raw($from_created_at)]);

		Schema::table('photos', function (Blueprint $table) {
			$table->index('month_day_key', 'photos_month_day_key_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_month_day_key_index');
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('month_day_key');
		});
	}
};
