<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const OAUTH = 'OAuth & SSO';
	public const USERS = 'Users Management';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('cat', self::OAUTH)->update(['cat' => self::USERS]);
		DB::table('configs')->where('key', '=', 'allow_username_change')->update(['cat' => self::USERS]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('cat', self::USERS)->update(['cat' => self::OAUTH]);
	}
};
