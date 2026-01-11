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
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('album_size_statistics');
		Schema::create('album_size_statistics', function (Blueprint $table) {
			// Primary key and foreign key to albums table
			$table->char('album_id', 24)->primary();
			$table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');

			// Size columns for each variant type (bigint unsigned for large filesizes)
			$table->unsignedBigInteger('size_thumb')->default(0);
			$table->unsignedBigInteger('size_thumb2x')->default(0);
			$table->unsignedBigInteger('size_small')->default(0);
			$table->unsignedBigInteger('size_small2x')->default(0);
			$table->unsignedBigInteger('size_medium')->default(0);
			$table->unsignedBigInteger('size_medium2x')->default(0);
			$table->unsignedBigInteger('size_original')->default(0);

			// No timestamps - this is a computed statistics table
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('album_size_statistics');
	}
};
