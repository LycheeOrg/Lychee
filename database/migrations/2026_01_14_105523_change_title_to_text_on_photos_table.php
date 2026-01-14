<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			// Drop the existing index
			if (Schema::hasIndex('photos', 'photos_album_id_is_starred_title_index')) {
				$table->dropIndex('photos_album_id_is_starred_title_index');
			}

			// Change to varchar 300 to support longer titles (though should not be necessary)
			// We do not use TEXT as the data will be stored on a different page which would slow down queries
			$table->string('title', 300)->nullable()->change();

			$table->index(['is_starred', 'title', 'taken_at', 'created_at'], 'photos_is_starred_title_taken_created_index');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			// Drop the existing index
			if (Schema::hasIndex('photos', 'photos_is_starred_title_taken_created_index')) {
				$table->dropIndex('photos_is_starred_title_taken_created_index');
			}

			// Change to varchar
			$table->string('title', 100)->nullable()->change();

			$table->index(['old_album_id', 'is_starred', 'title'], 'photos_album_id_is_starred_title_index');
		});
	}
};
