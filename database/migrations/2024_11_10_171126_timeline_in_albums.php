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
	private const ALBUMS = 'albums';
	private const BASE_ALBUMS = 'base_albums';
	private const ALBUM_TIMELINE_COLUMN_NAME = 'album_timeline';
	private const PHOTO_TIMELINE_COLUMN_NAME = 'photo_timeline';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ALBUMS, function ($table) {
			$table->string(self::ALBUM_TIMELINE_COLUMN_NAME, 20)->nullable()->default(null)->after('album_thumb_aspect_ratio');
		});
		Schema::table(self::BASE_ALBUMS, function ($table) {
			$table->string(self::PHOTO_TIMELINE_COLUMN_NAME, 20)->nullable()->default(null)->after('photo_layout');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::ALBUM_TIMELINE_COLUMN_NAME);
		});
		Schema::table(self::BASE_ALBUMS, function (Blueprint $table) {
			$table->dropColumn(self::PHOTO_TIMELINE_COLUMN_NAME);
		});
	}
};
