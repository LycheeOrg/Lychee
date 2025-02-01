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
		Schema::table('photos', function (Blueprint $table): void {
			$table->dateTime('initial_taken_at', 0)->nullable(true)->default(null)->after('taken_at_orig_tz')->comment('backup of the original taken_at value');
			$table->string('initial_taken_at_orig_tz', 31)->nullable(true)->default(null)->after('initial_taken_at')->comment('backup of the timezone at which the photo has originally been taken');
		});

		// Set initial values
		DB::table('photos')->update([
			'initial_taken_at' => DB::raw('taken_at'),
			'initial_taken_at_orig_tz' => DB::raw('taken_at_orig_tz'),
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('initial_taken_at_orig_tz');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('initial_taken_at');
		});
	}
};
