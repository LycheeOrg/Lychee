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
		Schema::table('jobs_history', function (Blueprint $table) {
			$table->index(['owner_id', 'status']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// We cannot remove index key because owner_id is used for FK constraint.
	}
};
