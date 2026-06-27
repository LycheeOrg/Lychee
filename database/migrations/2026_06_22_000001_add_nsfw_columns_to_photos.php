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
			$table->string('nsfw_status')->nullable()->default(null)->after('face_scan_status');
			$table->string('upload_trust_level')->nullable()->default(null)->after('nsfw_status');
		});

		DB::table('users')->where('upload_trust_level', '=', 'monitor')->update(['upload_trust_level' => 'check']);
		DB::table('configs')->where('key', '=', 'default_user_trust_level')->update(['type_range' => 'check|monitor|trust_but_verify|trusted']);
		DB::table('configs')->where('key', '=', 'guest_upload_trust_level')->update(['type_range' => 'check|monitor|trust_but_verify|trusted']);
	}

	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'default_user_trust_level')->update(['type_range' => 'check|monitor|trusted']);
		DB::table('configs')->where('key', '=', 'guest_upload_trust_level')->update(['type_range' => 'check|monitor|trusted']);

		Schema::table('photos', function (Blueprint $table): void {
			$table->dropColumn(['nsfw_status', 'upload_trust_level']);
		});
	}
};
