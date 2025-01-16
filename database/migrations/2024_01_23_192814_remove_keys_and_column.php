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
	public const TABLE = 'photos';
	public const COLUMN = 'is_public';

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
		Schema::table(self::TABLE, function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_public_index');
			$this->optimize->dropIndexIfExists($table, 'photos_album_id_is_starred_is_public_index');
		});
		Schema::disableForeignKeyConstraints();
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->dropColumn(self::COLUMN);
		});
		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE, function ($table) {
			$table->boolean(self::COLUMN)->nullable(false)->default(false);
		});
		Schema::table(self::TABLE, function (Blueprint $table) {
			$table->index(['album_id', self::COLUMN]);
			$table->index(['album_id', 'is_starred', self::COLUMN]);
		});
	}
};
