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
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('colours', function (Blueprint $table) {
			$table->unsignedMediumInteger('id')->primary();
			$table->unsignedTinyInteger('R')->default(0);
			$table->unsignedTinyInteger('G')->default(0);
			$table->unsignedTinyInteger('B')->default(0);
			$table->unique(['R', 'G', 'B']); // Ensure that the combination of R, G, and B is unique
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('colours');
	}
};
