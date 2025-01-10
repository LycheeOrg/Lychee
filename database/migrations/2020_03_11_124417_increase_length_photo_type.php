<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->string('type', 30)->default('')->change();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Log::warning(__FUNCTION__ . ':' . __LINE__ . ' There is no going back for ' . __CLASS__ . '!');
	}
};
