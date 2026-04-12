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
		Schema::table('users', function (Blueprint $table): void {
			$table->string('upload_trust_level', 20)->default('trusted')->after('quota_kb');
		});
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			$table->dropColumn('upload_trust_level');
		});
	}
};
