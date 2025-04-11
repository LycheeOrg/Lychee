<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const CREATED_AT_COL_NAME = 'created_at';
	private const DATETIME_PRECISION = 0;
	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('live_metrics', function (Blueprint $table) {
			$table->id();
			$table->dateTime(
				self::CREATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable();
			$table->string('visitor_id')->index();
			$table->string('action', 100)->index();
			$table->char('album_id', self::RANDOM_ID_LENGTH)->nullable(true);
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->nullable(true);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('live_metrics');
	}
};
