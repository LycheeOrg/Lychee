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
		Schema::table('access_permissions', function (Blueprint $table) {
			$table->boolean('grants_cover_access')->nullable(false)->default(false)->after('grants_full_photo_access');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('access_permissions', function (Blueprint $table) {
			$table->dropColumn('grants_cover_access');
		});
	}
};
