<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
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
		Schema::table('size_variants', function (Blueprint $table) {
			// This index is required by \App\Actions\SizeVariant\Delete::do()
			// for `SizeVariant::query()`
			$table->float('ratio')->after('height')->default(1);
			$table->index(['photo_id', 'type', 'ratio']);
		});

		DB::table('size_variants')
			->where('height', '>', 0)
			->update(['ratio' => DB::raw('width / height')]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('size_variants', function (Blueprint $table) {
			$this->optimize->dropIndexIfExists($table, 'size_variants_photo_id_type_ratio_index');
		});

		Schema::table('size_variants', function (Blueprint $table) {
			$table->dropColumn('ratio');
		});
	}
};
