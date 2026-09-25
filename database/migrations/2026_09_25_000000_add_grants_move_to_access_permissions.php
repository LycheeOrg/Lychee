<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 072 (FR-072-01/02): Move, Copy and Merge get their own grant,
 * separate from `grants_edit`. Existing shares keep what edit allowed them
 * before (Q-072-07): `grants_move` is backfilled from `grants_edit`.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('access_permissions', function (Blueprint $table): void {
			$table->boolean('grants_move')->nullable(false)->default(false)->after('grants_delete');
		});

		DB::table('access_permissions')->update(['grants_move' => DB::raw('grants_edit')]);
	}

	public function down(): void
	{
		Schema::table('access_permissions', function (Blueprint $table): void {
			$table->dropColumn('grants_move');
		});
	}
};
