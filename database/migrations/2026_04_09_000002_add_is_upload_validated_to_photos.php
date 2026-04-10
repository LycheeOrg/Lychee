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
		Schema::table('photos', function (Blueprint $table): void {
			$table->boolean('is_upload_validated')->default(true)->after('is_highlighted');
			$table->index('is_upload_validated');
		});
	}

	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table): void {
			$table->dropIndex(['is_upload_validated']);
			$table->dropColumn('is_upload_validated');
		});
	}
};
