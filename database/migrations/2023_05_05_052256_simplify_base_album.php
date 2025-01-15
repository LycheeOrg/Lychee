<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once 'TemporaryModels/OptimizeTables.php';

return new class() extends Migration {
	private const TABLE_NAME = 'access_permissions';
	private const TABLE_BASE_ALBUMS = 'base_albums';

	private const IS_LINK_REQUIRED = 'is_link_required';
	private const PASSWORD = 'password';
	private const GRANTS_FULL_PHOTO_ACCESS = 'grants_full_photo_access';
	private const GRANTS_DOWNLOAD = 'grants_download';
	private const IS_SHARE_BUTTON_VISIBLE = 'is_share_button_visible';
	private const IS_PUBLIC = 'is_public';

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
		$this->dropColumnsBaseAlbumTable();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->fixBaseAlbumTable();
		DB::transaction(fn () => $this->populateBaseAlbumTable());

		$this->optimize->exec();
	}

	private function dropColumnsBaseAlbumTable(): void
	{
		Schema::disableForeignKeyConstraints();
		Schema::table(self::TABLE_BASE_ALBUMS, function ($table) {
			$this->optimize->dropIndexIfExists($table, ['requires_link', self::IS_PUBLIC]);
			$this->optimize->dropIndexIfExists($table, [self::IS_PUBLIC, self::PASSWORD]);
			$table->dropColumn(self::IS_PUBLIC);
		});
		Schema::table(self::TABLE_BASE_ALBUMS, function ($table) {
			$table->dropColumn(self::GRANTS_FULL_PHOTO_ACCESS);
		});
		Schema::table(self::TABLE_BASE_ALBUMS, function ($table) {
			$table->dropColumn(self::GRANTS_DOWNLOAD);
		});
		Schema::table(self::TABLE_BASE_ALBUMS, function ($table) {
			$table->dropColumn(self::IS_LINK_REQUIRED);
		});
		Schema::table(self::TABLE_BASE_ALBUMS, function ($table) {
			$table->dropColumn(self::PASSWORD);
		});
		Schema::table(self::TABLE_BASE_ALBUMS, function ($table) {
			$table->dropColumn(self::IS_SHARE_BUTTON_VISIBLE);
		});
		Schema::enableForeignKeyConstraints();
	}

	/**
	 * Creates the table `base_albums`.
	 *
	 * The table `base_albums` contains all columns of the old table
	 * `albums` which are common to normal albums and tag albums.
	 */
	private function fixBaseAlbumTable(): void
	{
		Schema::table(self::TABLE_BASE_ALBUMS, function (Blueprint $table) {
			// Column definitions
			$table->boolean(self::IS_PUBLIC)->nullable(false)->default(false);
			$table->boolean(self::GRANTS_FULL_PHOTO_ACCESS)->nullable(false)->default(true);
			$table->boolean(self::IS_LINK_REQUIRED)->nullable(false)->default(false);
			$table->boolean(self::GRANTS_DOWNLOAD)->nullable(false)->default(false);
			$table->boolean(self::IS_SHARE_BUTTON_VISIBLE)->nullable(false)->default(false);
			$table->string(self::PASSWORD, 100)->nullable()->default(null);
			// These indices are required for efficient filtering for accessible and/or visible albums
			$table->index([self::IS_LINK_REQUIRED, self::IS_PUBLIC]); // for albums which don't require a direct link and are public
			$table->index([self::IS_PUBLIC, self::PASSWORD]); // for albums which are public and how no password
		});
	}

	private function populateBaseAlbumTable(): void
	{
		$publics = DB::table(self::TABLE_NAME)->whereNull('user_id')->get();
		foreach ($publics as $public) {
			DB::table(self::TABLE_BASE_ALBUMS)
			->where('id', '=', $public->base_album_id)
			->update([
				self::IS_PUBLIC => true, // Duh !
				self::IS_LINK_REQUIRED => $public->is_link_required,
				self::PASSWORD => $public->password,
				self::GRANTS_FULL_PHOTO_ACCESS => $public->grants_full_photo_access,
				self::GRANTS_DOWNLOAD => $public->grants_download,
			]);
		}
	}
};
