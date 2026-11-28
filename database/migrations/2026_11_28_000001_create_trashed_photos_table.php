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
 * Feature 119 (DO-119-01): photos waiting to be purged.
 *
 * A photo is put here when its last album link is removed by a delete. It is
 * hidden from every photo query from then on (`NotTrashedScope`), and a
 * purge deletes it later. The row goes with the photo (`ON DELETE CASCADE`).
 *
 * Rolling back drops the table: photos still waiting then show again to
 * their owner as photos without an album.
 */
return new class() extends Migration {
	private const RANDOM_ID_LENGTH = 24;

	public function up(): void
	{
		if (Schema::hasTable('trashed_photos')) {
			return;
		}

		Schema::create('trashed_photos', function (Blueprint $table) {
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->dateTime('trashed_at')->nullable(false);
			$table->primary('photo_id');
			$table->index('trashed_at');
			$table->foreign('photo_id')->references('id')->on('photos')->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('trashed_photos');
	}
};
