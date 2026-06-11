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
		Schema::create('purchasable_print_sizes', function (Blueprint $table) {
			$table->id();
			$table->foreignId('purchasable_id')->constrained()->onDelete('cascade');
			$table->foreignId('print_size_id')->constrained()->onDelete('cascade');
			$table->integer('price_cents')->nullable(false)->comment('Price in cents for this print size on this purchasable');

			$table->unique(['purchasable_id', 'print_size_id'], 'unique_purchasable_print_size');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('purchasable_print_sizes');
	}
};
