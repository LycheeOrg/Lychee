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
	private const ALBUM = 'albums';
	private const ASPECT_RATIO_COLUMN_NAME = 'album_thumb_aspect_ratio';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ALBUM, function ($table) {
			$table->string(self::ASPECT_RATIO_COLUMN_NAME, 6)->nullable()->default(null)->after('license');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ALBUM, function (Blueprint $table) {
			$table->dropColumn(self::ASPECT_RATIO_COLUMN_NAME);
		});
	}
};
