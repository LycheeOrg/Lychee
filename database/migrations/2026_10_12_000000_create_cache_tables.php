<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 090: tables of Laravel's `database` cache store.
 *
 * `cache_locks` holds the unique locks of the recompute jobs on asynchronous
 * queues, shared by the web and the worker containers (ADR-090-01). `cache`
 * makes `CACHE_DRIVER=database` usable.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::create('cache', function (Blueprint $table): void {
			$table->string('key')->primary();
			$table->mediumText('value');
			$table->bigInteger('expiration')->index();
		});

		Schema::create('cache_locks', function (Blueprint $table): void {
			$table->string('key')->primary();
			$table->string('owner');
			$table->bigInteger('expiration')->index();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('cache_locks');
		Schema::dropIfExists('cache');
	}
};
