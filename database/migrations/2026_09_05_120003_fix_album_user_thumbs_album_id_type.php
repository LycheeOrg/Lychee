<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

/**
 * `album_user_thumbs.album_id` holds either a `base_albums.id` (always
 * exactly 24 chars) or a `SmartAlbumType` enum value (e.g. `'unsorted'`,
 * `'recent'`) which is almost always shorter than 24 chars.
 *
 * Declaring it `char(24)` (as if every value were a fixed-length id) is
 * wrong for the smart-album case: MySQL/MariaDB strip the trailing padding
 * back off on SELECT, but PostgreSQL's `character(n)` keeps and returns it,
 * so a row seeded with `album_id = 'unsorted'` comes back as `'unsorted' .
 * str_repeat(' ', 16)` on Postgres - silently breaking every exact-string
 * lookup keyed by the un-padded enum value (e.g.
 * `AlbumSmartController::smart()`'s `$cached_covers[$smart_album->get_id()]`).
 * `varchar` never pads, so it behaves identically on every supported driver.
 */
return new class() extends Migration {
	private const TABLE_NAME = 'album_user_thumbs';
	private const ALBUM_ID = 'album_id';
	private const USER_ID_UNIQUE_KEY = 'user_id_unique_key';
	private const USER_ID_UNIQUE_KEY_NAME = 'album_user_thumbs_album_id_user_id_unique_key_unique';
	private const ALBUM_ID_INDEX_NAME = 'album_user_thumbs_album_id_index';
	private const RANDOM_ID_LENGTH = 24;

	private OptimizeTables $optimize;

	public function __construct()
	{
		$this->optimize = new OptimizeTables();
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$this->optimize->dropUniqueIfExists($table, self::USER_ID_UNIQUE_KEY_NAME);
			$this->optimize->dropIndexIfExists($table, self::ALBUM_ID_INDEX_NAME);

			$table->string(self::ALBUM_ID, self::RANDOM_ID_LENGTH)->change();

			$table->unique([self::ALBUM_ID, self::USER_ID_UNIQUE_KEY], self::USER_ID_UNIQUE_KEY_NAME);
			$table->index([self::ALBUM_ID], self::ALBUM_ID_INDEX_NAME);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$this->optimize->dropUniqueIfExists($table, self::USER_ID_UNIQUE_KEY_NAME);
			$this->optimize->dropIndexIfExists($table, self::ALBUM_ID_INDEX_NAME);

			$table->char(self::ALBUM_ID, self::RANDOM_ID_LENGTH)->change();

			$table->unique([self::ALBUM_ID, self::USER_ID_UNIQUE_KEY], self::USER_ID_UNIQUE_KEY_NAME);
			$table->index([self::ALBUM_ID], self::ALBUM_ID_INDEX_NAME);
		});
	}
};
