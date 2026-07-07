<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table): void {
			$table->boolean('is_validated')->default(true)->after('is_highlighted');
			$table->index('is_validated', 'photos_is_validated_index');
		});
	}

	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table): void {
			$this->optimize->dropIndexIfExists($table, 'photos_is_validated_index');
			$table->dropColumn('is_validated');
		});
	}
};
