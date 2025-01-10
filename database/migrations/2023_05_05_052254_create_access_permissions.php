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

	private const USER_ID = 'user_id';
	private const BASE_ALBUM_ID = 'base_album_id';
	private const IS_LINK_REQUIRED = 'is_link_required';
	private const PASSWORD = 'password';
	private const GRANTS_FULL_PHOTO_ACCESS = 'grants_full_photo_access';
	private const GRANTS_DOWNLOAD = 'grants_download';
	private const GRANTS_UPLOAD = 'grants_upload';
	private const GRANTS_EDIT = 'grants_edit';
	private const GRANTS_DELETE = 'grants_delete';

	private const CREATED_AT_COL_NAME = 'created_at';
	private const UPDATED_AT_COL_NAME = 'updated_at';
	private const DATETIME_PRECISION = 0;
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
		$this->createAccessPermissionTable();
		DB::transaction(fn () => $this->populateAccessPermissionTable());

		$this->optimize->exec();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists(self::TABLE_NAME);
	}

	/**
	 * Generate the table for access_permissions.
	 */
	private function createAccessPermissionTable(): void
	{
		// Any old data is not relevant.
		Schema::dropIfExists(self::TABLE_NAME);

		Schema::create(self::TABLE_NAME, function (Blueprint $table) {
			$table->bigIncrements('id');

			// User associated with the access capabilities
			// If null we consider the album public
			$table->unsignedInteger(self::USER_ID)->nullable()->default(null);

			// parentId = album ID
			$table->char(self::BASE_ALBUM_ID, self::RANDOM_ID_LENGTH)->nullable(true);

			// basic access rights for anonymous users.
			$table->boolean(self::IS_LINK_REQUIRED)->nullable(false)->default(false);
			$table->string(self::PASSWORD, 100)->nullable()->default(null);

			// Grants capabilities
			$table->boolean(self::GRANTS_FULL_PHOTO_ACCESS)->nullable(false)->default(false);
			$table->boolean(self::GRANTS_DOWNLOAD)->nullable(false)->default(false);
			$table->boolean(self::GRANTS_UPLOAD)->nullable(false)->default(false);
			$table->boolean(self::GRANTS_EDIT)->nullable(false)->default(false);
			$table->boolean(self::GRANTS_DELETE)->nullable(false)->default(false);

			$table->dateTime(self::CREATED_AT_COL_NAME, self::DATETIME_PRECISION)->nullable();
			$table->dateTime(self::UPDATED_AT_COL_NAME, self::DATETIME_PRECISION)->nullable();

			$table->index([self::USER_ID]); // for albums which are own by the currently authenticated user
			$table->index([self::BASE_ALBUM_ID]); // for albums which are own by the currently authenticated user

			// It is not possible to make a foreign key between base album and access permission.
			// The reason being that `recent` `starred` `on_this_day` are 3 smart albums which do not have an associated album
			// For this reason the only foreign key we consider is the one related to User.
			$table->foreign(self::USER_ID)->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			// This index is required to efficiently filter those albums
			// which are shared with a particular user
			$table->unique([self::BASE_ALBUM_ID, self::USER_ID]);
		});
	}

	private function populateAccessPermissionTable(): void
	{
		$baseAlbums = DB::table('base_albums')->where('is_public', '=', true)->get();
		foreach ($baseAlbums as $baseAlbum) {
			DB::table(self::TABLE_NAME)->insert([
				[
					self::USER_ID => null,
					self::BASE_ALBUM_ID => $baseAlbum->id,
					self::IS_LINK_REQUIRED => $baseAlbum->is_link_required,
					self::PASSWORD => $baseAlbum->password,
					self::GRANTS_FULL_PHOTO_ACCESS => $baseAlbum->grants_full_photo_access,
					self::GRANTS_DOWNLOAD => $baseAlbum->grants_download,
					self::GRANTS_UPLOAD => false,
					self::GRANTS_EDIT => false,
					self::GRANTS_DELETE => false,
				],
			]);
		}

		// Loop over every base album
		$currentShares = DB::table('user_base_album')
			->join('base_albums', 'base_albums.id', '=', 'user_base_album.base_album_id')
			->select([
				self::BASE_ALBUM_ID,
				self::USER_ID,
				self::GRANTS_DOWNLOAD,
				self::GRANTS_FULL_PHOTO_ACCESS,
			])
			->get();

		foreach ($currentShares as $share) {
			DB::table(self::TABLE_NAME)->
			insert([
				[
					self::USER_ID => $share->user_id,
					self::BASE_ALBUM_ID => $share->base_album_id,
					self::IS_LINK_REQUIRED => false,
					self::PASSWORD => null,
					self::GRANTS_FULL_PHOTO_ACCESS => $share->grants_full_photo_access,
					self::GRANTS_DOWNLOAD => $share->grants_download,
					self::GRANTS_UPLOAD => false,
					self::GRANTS_EDIT => false,
					self::GRANTS_DELETE => false,
				],
			]);
		}
	}
};
