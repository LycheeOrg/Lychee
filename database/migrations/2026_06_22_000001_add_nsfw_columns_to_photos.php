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
	}

	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table): void {
			$table->dropColumn(['nsfw_status', 'upload_trust_level']);
		});
	}
};
