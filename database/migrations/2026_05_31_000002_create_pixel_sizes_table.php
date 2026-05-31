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
		Schema::create('pixel_sizes', function (Blueprint $table) {
			$table->id();
			$table->string('label', 100)->nullable(false)->comment('Display label, e.g. "3000×2000 px"');
			$table->unsignedInteger('width')->nullable(false)->comment('Width in pixels');
			$table->unsignedInteger('height')->nullable(false)->comment('Height in pixels');
			$table->boolean('is_active')->nullable(false)->default(true)->comment('Whether this size is visible to customers');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('pixel_sizes');
	}
};
