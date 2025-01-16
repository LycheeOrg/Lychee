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

/**
 * Class AddIndexForDelete.
 *
 * Adds an index which is required to efficiently find all paths of
 * size variants which can be safely deleted without breaking shared use of
 * the media file by a duplicate.
 */
return new class() extends Migration {
	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	public function up(): void
	{
		Schema::table('size_variants', function (Blueprint $table) {
			// This index is required by \App\Actions\SizeVariant\Delete::do()
			// for `SizeVariant::query()`
			$table->index(['short_path']);
		});
	}

	public function down(): void
	{
		Schema::table('size_variants', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'size_variants_short_path_index');
		});
	}
};
