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
	public const RANDOM_ID_LENGTH = 24;
	private const CREATED_AT_COL_NAME = 'created_at';
	private const UPDATED_AT_COL_NAME = 'updated_at';
	private const DATETIME_PRECISION = 0;

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('jobs_history', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedInteger('owner_id');
			$table->string('job', 200); // brief description of the job
			$table->char('parent_id', self::RANDOM_ID_LENGTH)->nullable(true); // parentId = album ID
			$table->integer('status')->default(0); // 0 - not run, 1 success, 2 failure

			$table->dateTime(
				self::CREATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable();
			$table->dateTime(
				self::UPDATED_AT_COL_NAME,
				self::DATETIME_PRECISION
			)->nullable();

			$table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::disableForeignKeyConstraints();
		Schema::dropIfExists('jobs_history');
		Schema::enableForeignKeyConstraints();
	}
};
