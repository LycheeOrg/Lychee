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
 * Feature 115: per-user import from server (FR-115-01, FR-115-14, ADR-115-01).
 *
 * `null` disables import from server, `/` gives full access,
 * any other absolute path restricts the user to that folder.
 * The user named by `owner_id` keeps the access they had before.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			$table->string('server_path', 4096)->nullable()->default(null)->after('upload_trust_level');
		});

		$this->grantOwner();
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			$table->dropColumn('server_path');
		});
	}

	/**
	 * Give the `owner_id` user full access (no-op when there is none).
	 */
	public function grantOwner(): void
	{
		$owner_id = intval(DB::table('configs')->where('key', '=', 'owner_id')->value('value') ?? 0);
		DB::table('users')->where('id', '=', $owner_id)->update(['server_path' => '/']);
	}
};
