<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Artisan::call('lychee:fix-permissions', ['--dry-run' => 1]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
	}
};
