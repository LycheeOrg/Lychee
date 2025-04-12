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
	private const COL_VISIT = 'visit_count';
	private const COL_DOWNLOAD = 'download_count';
	private const COL_FAVOURITE = 'favourite_count';
	private const COL_SHARED = 'shared_count';
	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('statistics', function (Blueprint $table) {
			$table->id();
			$table->char('album_id', self::RANDOM_ID_LENGTH)->index()->unique()->nullable(true);
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->index()->unique()->nullable(true);
			$table->unsignedBigInteger(self::COL_VISIT)->default(0)->comment('Number of times this photo/album has been viewed');
			$table->unsignedBigInteger(self::COL_DOWNLOAD)->default(0)->comment('Number of times this photo/album has been downloaded (excluding albums)');
			$table->unsignedBigInteger(self::COL_FAVOURITE)->default(0)->comment('Number of times this photo has been favourite');
			$table->unsignedBigInteger(self::COL_SHARED)->default(0)->comment('Number of times this photo/album has been shared');
		});

		DB::statement('INSERT INTO statistics (photo_id) SELECT id FROM photos');
		DB::statement('INSERT INTO statistics (album_id) SELECT id FROM base_albums');

		DB::table('configs')->where('key', 'client_side_favourite_enabled')->update(['order' => 1]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('statistics');

		DB::table('configs')->where('key', 'client_side_favourite_enabled')->update(['order' => 32767]);
	}
};
