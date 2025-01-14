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
		Schema::dropIfExists('user_base_album');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->createUserBaseAlbumTable();
		DB::transaction(fn () => $this->populateUserBaseAlbumTable());

		$this->optimize->exec();
	}

	private function createUserBaseAlbumTable(): void
	{
		Schema::create('user_base_album', function (Blueprint $table) {
			// Column definitions
			$table->bigIncrements('id')->nullable(false);
			$table->unsignedInteger(self::USER_ID)->nullable(false);
			$table->char(self::BASE_ALBUM_ID, self::RANDOM_ID_LENGTH)->nullable(false);
			// Indices and constraint definitions
			$table->foreign(self::USER_ID)->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign(self::BASE_ALBUM_ID)->references('id')->on('base_albums')->cascadeOnUpdate()->cascadeOnDelete();
			// This index is required to efficiently filter those albums
			// which are shared with a particular user
			$table->unique([self::BASE_ALBUM_ID, self::USER_ID]);
		});
	}

	private function populateUserBaseAlbumTable(): void
	{
		$shared = DB::table(self::TABLE_NAME)->whereNotNull(self::USER_ID)->get();
		foreach ($shared as $share) {
			DB::table('user_base_album')->
				insert([[
					self::USER_ID => $share->user_id,
					self::BASE_ALBUM_ID => $share->base_album_id,
				]]);
		}
	}
};
