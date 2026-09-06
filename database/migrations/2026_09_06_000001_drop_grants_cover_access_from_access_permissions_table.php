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
 * `grants_cover_access` was a per-album permission introduced alongside
 * `is_locked` on {@see \App\Http\Resources\Models\ThumbAlbumResource}.
 * It has been superseded by the global `show_cover_of_locked_albums` and
 * `show_selected_cover_on_locked_albums` configs (see
 * database/migrations/2026_09_06_000000_add_locked_album_cover_configs.php)
 * to avoid the access-rights propagation complexity of a per-album toggle.
 */
return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('access_permissions', function (Blueprint $table) {
			$table->dropColumn('grants_cover_access');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('access_permissions', function (Blueprint $table) {
			$table->boolean('grants_cover_access')->nullable(false)->default(false)->after('grants_full_photo_access');
		});
	}
};
