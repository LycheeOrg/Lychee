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
	private const USER_GROUP_ID = 'user_group_id';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('access_permissions', function ($table) {
			$table->unsignedInteger(self::USER_GROUP_ID)->nullable()->default(null)->after('user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('access_permissions', function (Blueprint $table) {
			$table->dropColumn(self::USER_GROUP_ID);
		});
	}
};
