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
	public function up(): void
	{
		Schema::table('order_items', function (Blueprint $table) {
			$table->unsignedBigInteger('size_variant_id')->nullable(true)->after('photo_id')->comment('Direct access to the size variant purchased');
			$table->foreign('size_variant_id')->references('id')->on('size_variants');
			$table->string('download_link', 191)->nullable(true)->after('item_notes')->comment('Download link for the item if applicable');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('order_items', function (Blueprint $table) {
			$table->dropColumn('download_link');
		});
		Schema::table('order_items', function (Blueprint $table) {
			$table->dropForeign(['size_variant_id']);
		});
		Schema::table('order_items', function (Blueprint $table) {
			$table->dropColumn('size_variant_id');
		});
	}
};
