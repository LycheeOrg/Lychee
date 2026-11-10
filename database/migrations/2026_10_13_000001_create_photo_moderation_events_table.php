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
	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('photo_moderation_events', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->string('event', 16)->nullable(false);
			$table->string('source', 16)->nullable(false);
			$table->unsignedInteger('user_id')->nullable(true);
			$table->timestamp('created_at')->nullable(false);

			$table->index('photo_id');
			$table->foreign('photo_id')->references('id')->on('photos')->cascadeOnDelete();
			$table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('photo_moderation_events');
	}
};
