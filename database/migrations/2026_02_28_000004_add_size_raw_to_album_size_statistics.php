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
 * Add size_raw column to album_size_statistics table for RAW size variant tracking.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('album_size_statistics', function (Blueprint $table) {
			$table->unsignedBigInteger('size_raw')->default(0)->after('album_id');
		});
	}

	public function down(): void
	{
		Schema::table('album_size_statistics', function (Blueprint $table) {
			$table->dropColumn('size_raw');
		});
	}
};
