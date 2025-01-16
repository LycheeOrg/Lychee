<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const TABLE_NAME = 'oauth_credentials';

	public const USER_ID = 'user_id';
	public const PROVIDER = 'provider';
	public const TOKEN_ID = 'token_id';

	private const CREATED_AT_COL_NAME = 'created_at';
	private const UPDATED_AT_COL_NAME = 'updated_at';
	private const DATETIME_PRECISION = 0;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// Any old data is not relevant.
		Schema::dropIfExists(self::TABLE_NAME);

		Schema::create(self::TABLE_NAME, function (Blueprint $table) {
			$table->bigIncrements('id');

			// User associated with the access capabilities
			// If null we consider the album public
			$table->unsignedInteger(self::USER_ID)->nullable()->default(null);

			$table->string(self::PROVIDER, 20);
			$table->string(self::TOKEN_ID);

			$table->dateTime(self::CREATED_AT_COL_NAME, self::DATETIME_PRECISION)->nullable();
			$table->dateTime(self::UPDATED_AT_COL_NAME, self::DATETIME_PRECISION)->nullable();

			$table->index([self::USER_ID]); // for credentials which are own by the currently authenticated user
			$table->index([self::TOKEN_ID]);
			$table->unique([self::TOKEN_ID]);
			$table->index([self::TOKEN_ID, self::PROVIDER]);
			$table->unique([self::PROVIDER, self::USER_ID]);
			$table->foreign(self::USER_ID)->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
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
