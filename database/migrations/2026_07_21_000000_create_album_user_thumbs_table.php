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
	private const TABLE_NAME = 'album_user_thumbs';
	private const USER_ID = 'user_id';
	private const ALBUM_ID = 'album_id';
	private const PHOTO_ID = 'photo_id';
	private const RANDOM_ID_LENGTH = 24;

	// Generated column coalescing the nullable USER_ID so a plain unique index
	// can enforce uniqueness even for guest (NULL) rows. A unique index
	// ignores NULL, so [album_id, user_id] alone lets concurrent guest
	// requests (see CachesAlbumUserThumb::getCachedOrLiveThumb()'s
	// updateOrCreate()) insert multiple (album_id, NULL) rows, after which
	// reads may return an arbitrary one. Mirrors the same fix already applied
	// to access_permissions, see
	// 2026_07_01_120000_deduplicate_and_constrain_access_permissions.php.
	private const USER_ID_UNIQUE_KEY = 'user_id_unique_key';

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

			// No ->nullable(false): MariaDB's grammar rejects an explicit NOT
			// NULL clause on generated columns entirely, so Laravel must omit
			// it here. COALESCE already guarantees the stored value is never
			// actually NULL. SQLite only allows VIRTUAL (not STORED) generated
			// columns to be added after the fact, but both are fine to declare
			// in a fresh CREATE TABLE like this one; VIRTUAL is used there
			// anyway since these columns only exist to be indexed, not read.
			if (DB::getDriverName() === 'sqlite') {
				$table->unsignedInteger(self::USER_ID_UNIQUE_KEY)->virtualAs('COALESCE(' . self::USER_ID . ', 0)');
			} else {
				$table->unsignedInteger(self::USER_ID_UNIQUE_KEY)->storedAs('COALESCE(' . self::USER_ID . ', 0)');
			}

			$table->unique([self::ALBUM_ID, self::USER_ID_UNIQUE_KEY]);
			$table->index([self::ALBUM_ID]);

			// MySQL/MariaDB prohibit a CASCADE or SET NULL referential action on
			// a column that a generated column depends on (user_id_unique_key
			// depends on user_id here) - only RESTRICT/NO ACTION are legal. This
			// means deleting a user no longer cascades into this table at the DB
			// level; App\Models\User::delete() explicitly deletes the user's
			// AlbumUserThumb rows first to compensate (mirrors how it already
			// handles AccessPermission).
			$table->foreign(self::USER_ID)->references('id')->on('users')->restrictOnUpdate()->restrictOnDelete();
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
