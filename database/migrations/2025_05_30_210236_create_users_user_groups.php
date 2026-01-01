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
	private const USER_ID = 'user_id';
	private const USER_GROUP_ID = 'user_group_id';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('users_user_groups', function (Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger(self::USER_ID)->nullable(false);
			$table->unsignedInteger(self::USER_GROUP_ID)->nullable(false);
			$table->string('role', 50)->nullable(false)->default('member');
			$table->dateTime('created_at', 0)->nullable(true)->default(DB::raw('CURRENT_TIMESTAMP'));

			$table->index([self::USER_ID]);
			$table->index([self::USER_GROUP_ID]);
			$table->index([self::USER_ID, self::USER_GROUP_ID]);
			$table->unique([self::USER_ID, self::USER_GROUP_ID]);
			$table->foreign(self::USER_ID)->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreign(self::USER_GROUP_ID)->references('id')->on('user_groups')->cascadeOnUpdate()->cascadeOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('users_user_groups');
	}
};
