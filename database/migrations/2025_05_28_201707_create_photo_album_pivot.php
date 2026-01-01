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
	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('photo_album');
		Schema::create('photo_album', function (Blueprint $table) {
			$table->char('album_id', self::RANDOM_ID_LENGTH)->index()->nullable(false);
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->index()->nullable(false);
			$table->primary(['photo_id', 'album_id']);
			$table->index(['album_id', 'photo_id']);
			$table->foreign('photo_id')->references('id')->on('photos');
			$table->foreign('album_id')->references('id')->on('albums');
		});

		DB::statement('INSERT INTO photo_album (photo_id, album_id) SELECT id, album_id FROM photos WHERE album_id IS NOT NULL');

		Schema::table('photos', function (Blueprint $table) { $table->dropForeign(['album_id']); });
		Schema::table('photos', function (Blueprint $table) { $table->renameColumn('album_id', 'old_album_id'); });
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('photo_album');

		Schema::table('photos', function (Blueprint $table) {
			$table->renameColumn('old_album_id', 'album_id');
			$table->foreign('album_id')->references('id')->on('albums');
		});
	}
};
