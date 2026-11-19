<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Make some space.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'sync_delete_missing_photos')->update(['order' => 25]);
		DB::table('configs')->where('key', '=', 'sync_delete_missing_albums')->update(['order' => 26]);
		DB::table('configs')->where('key', '=', 'sync_dry_run')->update(['order' => 27]);
		DB::table('configs')->where('key', '=', 'extract_zip_on_upload')->update(['order' => 28]);
		DB::table('configs')->where('key', '=', 'close_upload_on_success')->update(['order' => 29]);
		DB::table('configs')->where('key', '=', 'folder_upload_enabled')->update(['order' => 35]);
		DB::table('configs')->where('key', '=', 'folder_upload_max_depth')->update(['order' => 36]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'sync_delete_missing_photos')->update(['order' => 24]);
		DB::table('configs')->where('key', '=', 'sync_delete_missing_albums')->update(['order' => 25]);
		DB::table('configs')->where('key', '=', 'sync_dry_run')->update(['order' => 26]);
		DB::table('configs')->where('key', '=', 'extract_zip_on_upload')->update(['order' => 27]);
		DB::table('configs')->where('key', '=', 'close_upload_on_success')->update(['order' => 28]);
		DB::table('configs')->where('key', '=', 'folder_upload_enabled')->update(['order' => 30]);
		DB::table('configs')->where('key', '=', 'folder_upload_max_depth')->update(['order' => 31]);
	}
};
