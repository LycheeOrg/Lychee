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
	private const TAG_ID = 'tag_id';
	private const PHOTO_ID = 'photo_id';
	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('tags', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name', 100)->nullable(false)->unique();
			$table->string('description', 255)->nullable(true);
			$table->index('name');
		});

		Schema::create('photos_tags', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger(self::TAG_ID)->nullable(false);
			$table->char(self::PHOTO_ID, self::RANDOM_ID_LENGTH)->nullable(false);

			$table->index([self::TAG_ID]);
			$table->index([self::PHOTO_ID]);
			$table->index([self::TAG_ID, self::PHOTO_ID]);
			$table->unique([self::TAG_ID, self::PHOTO_ID]);
			$table->foreign(self::TAG_ID)->references('id')->on('tags')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign(self::PHOTO_ID)->references('id')->on('photos')->cascadeOnUpdate()->cascadeOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('photos_tags');
		Schema::dropIfExists('tags');
	}
};
