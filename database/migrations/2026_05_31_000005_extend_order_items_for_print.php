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
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('order_items', function (Blueprint $table) {
			$table->boolean('is_print')->nullable(false)->default(false)->after('download_link')
				->comment('True when this item requires physical print fulfilment');
			$table->foreignId('print_size_id')->nullable()->constrained()->nullOnDelete()->after('is_print');
			$table->foreignId('pixel_size_id')->nullable()->constrained()->nullOnDelete()->after('print_size_id');
			$table->unsignedInteger('print_width')->nullable()->after('pixel_size_id');
			$table->unsignedInteger('print_height')->nullable()->after('print_width');
			$table->string('print_unit', 10)->nullable()->after('print_height');
			$table->string('print_paper_type', 100)->nullable()->after('print_unit');
			$table->unsignedInteger('pixel_width')->nullable()->after('print_paper_type');
			$table->unsignedInteger('pixel_height')->nullable()->after('pixel_width');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('order_items', function (Blueprint $table) {
			$table->dropConstrainedForeignId('print_size_id');
			$table->dropConstrainedForeignId('pixel_size_id');
			$table->dropColumn([
				'is_print',
				'print_width',
				'print_height',
				'print_unit',
				'print_paper_type',
				'pixel_width',
				'pixel_height',
			]);
		});
	}
};
