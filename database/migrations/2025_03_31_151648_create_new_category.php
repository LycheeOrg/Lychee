<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const CAT = 'access_permissions';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('config_categories')->insert([
			[
				'cat' => self::CAT,
				'name' => 'Permissions',
				'description' => '',
				'order' => 7,
			],
		]);

		DB::table('configs')->where('key', 'grants_download')->update(['cat' => self::CAT, 'order' => 3]);
		DB::table('configs')->where('key', 'grants_full_photo_access')->update(['cat' => self::CAT, 'order' => 4]);
		DB::table('configs')->where('key', 'share_button_visible')->update(['cat' => self::CAT, 'order' => 5]);
		DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update(['cat' => self::CAT, 'order' => 6]);
		DB::table('configs')->where('key', 'login_required')->update(['cat' => self::CAT, 'order' => 1]);
		DB::table('configs')->where('key', 'login_required_root_only')->update(['cat' => self::CAT, 'order' => 2]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'grants_download')->update(['cat' => 'Gallery', 'order' => 20]);
		DB::table('configs')->where('key', 'grants_full_photo_access')->update(['cat' => 'Gallery', 'order' => 21]);
		DB::table('configs')->where('key', 'share_button_visible')->update(['cat' => 'Gallery', 'order' => 22]);
		DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update(['cat' => 'Gallery', 'order' => 23]);
		DB::table('configs')->where('key', 'login_required')->update(['cat' => 'Gallery', 'order' => 24]);
		DB::table('configs')->where('key', 'login_required_root_only')->update(['cat' => 'Gallery', 'order' => 25]);

		DB::table('config_categories')->where('cat', self::CAT)->delete();
	}
};
