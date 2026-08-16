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
		Schema::create('landing_featured_items', function (Blueprint $table): void {
			$table->ulid('id')->primary();
			$table->string('item_type', 10); // LandingFeaturedItemType: photo | album
			$table->string('item_id');
			$table->integer('sort_order')->default(0);
			$table->boolean('enabled')->default(true);
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);

			$table->index('enabled');
			$table->index('item_type');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('landing_featured_items');
	}
};
