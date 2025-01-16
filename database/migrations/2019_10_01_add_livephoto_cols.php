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
		Schema::table('photos', function ($table) {
			$table->string('livePhotoUrl')->default(null)->after('thumbURL')->nullable();
		});

		Schema::table('photos', function ($table) {
			$table->string('livePhotoContentID')->default(null)->after('thumb2x')->nullable();
		});

		Schema::table('photos', function ($table) {
			$table->string('livePhotoChecksum', 40)->default(null)->after('checksum')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('livePhotoContentID');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('livePhotoUrl');
		});
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('livePhotoChecksum');
		});
	}
};
