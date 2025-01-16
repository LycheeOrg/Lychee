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
	private const BASE_ALBUM = 'base_albums';
	private const PHOTO_LAYOUT_COLUMN_NAME = 'photo_layout';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::BASE_ALBUM, function ($table) {
			$table->string(self::PHOTO_LAYOUT_COLUMN_NAME, 20)->nullable()->default(null)->after('copyright');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::BASE_ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::PHOTO_LAYOUT_COLUMN_NAME);
		});
	}
};
