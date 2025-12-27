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
	public const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('photo_ratings', function (Blueprint $table) {
			$table->id();
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->index();
			$table->unsignedInteger('user_id')->index();
			$table->unsignedTinyInteger('rating')->comment('Rating value 1-5');

			// Unique constraint: one rating per user per photo
			$table->unique(['photo_id', 'user_id']);

			// Foreign key constraints with CASCADE delete
			$table->foreign('photo_id')
				->references('id')
				->on('photos')
				->onDelete('cascade');

			$table->foreign('user_id')
				->references('id')
				->on('users')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('photo_ratings');
	}
};
