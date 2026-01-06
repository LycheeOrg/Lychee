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
	public const PHOTO_ID = 'photo_id';
	public const RANDOM_ID_LENGTH = 24; // Length of the random ID

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('palettes', function (Blueprint $table) {
			$table->id();
			$table->char(self::PHOTO_ID, self::RANDOM_ID_LENGTH)->nullable(false);
			$table->unsignedMediumInteger('colour_1')->nullable(false);
			$table->unsignedMediumInteger('colour_2')->nullable(false);
			$table->unsignedMediumInteger('colour_3')->nullable(false);
			$table->unsignedMediumInteger('colour_4')->nullable(false);
			$table->unsignedMediumInteger('colour_5')->nullable(false);

			$table->index('id');
			$table->index([self::PHOTO_ID]);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('palettes');
	}
};
