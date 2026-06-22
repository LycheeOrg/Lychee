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
	public function up(): void
	{
		Schema::create('nsfw_detections', function (Blueprint $table): void {
			$table->bigIncrements('id');
			$table->string('photo_id');
			$table->string('label');
			$table->float('confidence');
			$table->integer('bbox_x');
			$table->integer('bbox_y');
			$table->integer('bbox_width');
			$table->integer('bbox_height');
			$table->integer('area_pixels')->nullable();
			$table->float('area_ratio')->nullable();
			$table->boolean('is_block')->default(false);
			$table->boolean('is_review')->default(false);
			$table->boolean('is_sensitive')->default(false);
			$table->timestamp('created_at')->nullable();

			$table->foreign('photo_id')->references('id')->on('photos')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('nsfw_detections');
	}
};
