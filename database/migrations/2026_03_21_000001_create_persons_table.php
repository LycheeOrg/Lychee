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
	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('persons', function (Blueprint $table) {
			$table->char('id', self::RANDOM_ID_LENGTH)->primary();
			$table->string('name', 255)->nullable(false);
			$table->unsignedInteger('user_id')->nullable(true)->unique();
			$table->boolean('is_searchable')->default(true);
			$table->timestamps();

			$table->index('user_id');
			$table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('persons');
	}
};
