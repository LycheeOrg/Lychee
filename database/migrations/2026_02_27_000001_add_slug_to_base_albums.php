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
	public function up(): void
	{
		Schema::table('base_albums', function (Blueprint $table): void {
			$table->string('slug', 250)->nullable()->unique()->after('title');
		});
	}

	public function down(): void
	{
		Schema::table('base_albums', function (Blueprint $table): void {
			$table->dropUnique(['slug']);
			$table->dropColumn('slug');
		});
	}
};
