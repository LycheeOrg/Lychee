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
 * Feature 118: share and edit rights, separate from upload (FR-118-01, FR-118-08, ADR-118-01).
 *
 * Every user keeps what they could do: both rights start as `may_upload`,
 * which also satisfies the rule that upload implies share and edit (FR-118-22).
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			$table->boolean('may_share')->nullable(false)->default(false)->after('may_upload');
			$table->boolean('may_edit')->nullable(false)->default(false)->after('may_share');
		});

		$this->backfill();
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			$table->dropColumn(['may_share', 'may_edit']);
		});
	}

	/**
	 * Set both rights to the user's upload right.
	 */
	public function backfill(): void
	{
		DB::table('users')->update([
			'may_share' => DB::raw('may_upload'),
			'may_edit' => DB::raw('may_upload'),
		]);
	}
};
