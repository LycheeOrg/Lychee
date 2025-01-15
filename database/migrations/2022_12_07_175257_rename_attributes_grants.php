<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (DB::getDriverName() === 'sqlite') {
			Schema::disableForeignKeyConstraints();
		}
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('requires_link', 'is_link_required');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('is_downloadable', 'grants_download');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('grants_full_photo', 'grants_full_photo_access');
		});
		if (DB::getDriverName() === 'sqlite') {
			Schema::enableForeignKeyConstraints();
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (DB::getDriverName() === 'sqlite') {
			Schema::disableForeignKeyConstraints();
		}
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('is_link_required', 'requires_link');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('grants_download', 'is_downloadable');
		});
		Schema::table('base_albums', function (Blueprint $table) {
			$table->renameColumn('grants_full_photo_access', 'grants_full_photo');
		});
		if (DB::getDriverName() === 'sqlite') {
			Schema::enableForeignKeyConstraints();
		}
	}
};
