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
		Schema::create('landing_links', function (Blueprint $table): void {
			$table->ulid('id')->primary();
			$table->string('label', 255);
			$table->string('url', 2048);
			$table->string('placement', 20); // LandingLinkPlacement: nav | footer | both
			$table->boolean('open_in_new_tab')->default(true);
			$table->integer('sort_order')->default(0);
			$table->boolean('enabled')->default(true);
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);

			$table->index('enabled');
			$table->index('placement');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('landing_links');
	}
};
