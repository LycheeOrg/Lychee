<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const SQL_TIMEZONE_NAME = 'UTC';
	private const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('page_contents');
		Schema::dropIfExists('pages');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @throws InvalidFormatException
	 */
	public function down(): void
	{
		$strNow = Carbon::now(
			new DateTimeZone(self::SQL_TIMEZONE_NAME)
		)->format(self::SQL_DATETIME_FORMAT);

		Schema::dropIfExists('pages');
		Schema::create('pages', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title', 150)->default('');
			$table->string('menu_title', 100)->default('');
			$table->boolean('in_menu')->default(false);
			$table->boolean('enabled')->default(false);
			$table->string('link', 150)->default('');
			$table->integer('order')->default(0);
			$table->dateTime('created_at', 0)->nullable(false);
			$table->dateTime('updated_at', 0)->nullable(false);
		});

		DB::table('pages')->insert([
			[
				'title' => 'gallery',
				'menu_title' => 'gallery',
				'in_menu' => true,
				'link' => '/gallery',
				'enabled' => true,
				'order' => 2,
				'created_at' => $strNow,
				'updated_at' => $strNow, // also set `updated_at` to ensure that `updated_at` is not before `created_at`
			],
		]);

		Schema::dropIfExists('page_contents');
		Schema::create('page_contents', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('page_id')->unsigned();
			$table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
			$table->text('content');
			$table->string('class', 150);
			$table->enum('type', ['div', 'img']);
			$table->integer('order')->default(0);
			$table->dateTime('created_at', 0)->nullable(false);
			$table->dateTime('updated_at', 0)->nullable(false);
		});
	}
};
