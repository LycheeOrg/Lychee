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
	private const TAG_ID = 'tag_id';
	private const ALBUM_ID = 'album_id';
	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('albums_tags', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger(self::TAG_ID)->nullable(false);
			$table->char(self::ALBUM_ID, self::RANDOM_ID_LENGTH)->nullable(false);

			$table->index([self::TAG_ID]);
			$table->index([self::ALBUM_ID]);
			$table->index([self::TAG_ID, self::ALBUM_ID]);
			$table->unique([self::TAG_ID, self::ALBUM_ID]);
			$table->foreign(self::TAG_ID)->references('id')->on('tags')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign(self::ALBUM_ID)->references('id')->on('albums')->cascadeOnUpdate()->cascadeOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('albums_tags');
	}
};
