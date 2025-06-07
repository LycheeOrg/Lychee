<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'default_user_quota')->update(['order' => 2]);
		DB::table('configs')->where('key', 'oauth_grant_new_user_modification_rights')->update(['key' => 'grant_new_user_modification_rights']);
		DB::table('configs')->where('key', 'oauth_grant_new_user_upload_rights')->update(['key' => 'grant_new_user_upload_rights']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'grant_new_user_modification_rights')->update(['key' => 'oauth_grant_new_user_modification_rights']);
		DB::table('configs')->where('key', 'grant_new_user_upload_rights')->update(['key' => 'oauth_grant_new_user_upload_rights']);
	}
};
