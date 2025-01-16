<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	public const ACCESS_PERMISSIONS = 'access_permissions';

	// Id names
	public const BASE_ALBUM_ID = 'base_album_id';
	public const USER_ID = 'user_id';

	// Attributes name
	public const IS_LINK_REQUIRED = 'is_link_required';
	public const PASSWORD = 'password';

	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::ACCESS_PERMISSIONS, function (Blueprint $table) {
			$table->index([self::IS_LINK_REQUIRED]); // for albums which don't require a direct link and are public
			$table->index([self::IS_LINK_REQUIRED, self::PASSWORD]); // for albums which are public and how no password
		});

		$this->optimize->exec();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::ACCESS_PERMISSIONS, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, self::ACCESS_PERMISSIONS . '_' . self::IS_LINK_REQUIRED . '_index');
			$this->optimize->dropIndexIfExists($table, self::ACCESS_PERMISSIONS . '_' . self::IS_LINK_REQUIRED . '_' . self::PASSWORD . '_index');
		});
	}
};
