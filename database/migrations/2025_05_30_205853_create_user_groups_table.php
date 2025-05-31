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
		Schema::create('user_groups', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 100)->nullable(false)->unique();
			$table->string('description', 255)->nullable(true);
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);
			$table->index('id');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('user_groups');
	}
};
