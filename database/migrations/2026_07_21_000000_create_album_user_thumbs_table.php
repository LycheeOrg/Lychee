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
	private const TABLE_NAME = 'album_user_thumbs';
	private const USER_ID = 'user_id';
	private const ALBUM_ID = 'album_id';
	private const PHOTO_ID = 'photo_id';
	private const RANDOM_ID_LENGTH = 24;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create(self::TABLE_NAME, function (Blueprint $table) {
			$table->bigIncrements('id');

			// Null means "the public/guest view of this album".
			$table->unsignedInteger(self::USER_ID)->nullable();

			// Holds either a base_albums.id (tag/person albums) or a SmartAlbumType
			// enum value (e.g. 'recent'). Smart albums have no associated DB row,
			// so - like access_permissions.base_album_id - this column has no FK.
			$table->char(self::ALBUM_ID, self::RANDOM_ID_LENGTH);

			$table->char(self::PHOTO_ID, self::RANDOM_ID_LENGTH);

			$table->unique([self::ALBUM_ID, self::USER_ID]);
			$table->index([self::ALBUM_ID]);

			$table->foreign(self::USER_ID)->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign(self::PHOTO_ID)->references('id')->on('photos')->cascadeOnUpdate()->cascadeOnDelete();

			// No timestamps - this is a computed cache table.
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists(self::TABLE_NAME);
	}
};
