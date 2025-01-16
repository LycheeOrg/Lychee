<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MariaDB [lychee]> show columns from lychee_albums;
// +--------------+---------------------+------+-----+---------+-------+
// | Field        | Type                | Null | Key | Default | Extra |
// +--------------+---------------------+------+-----+---------+-------+
// | id           | bigint(14) unsigned | NO   | PRI | NULL    |       |
// | title        | varchar(100)        | NO   |     |         |       |
// | description  | varchar(1000)       | YES  |     |         |       |
// | sysstamp     | int(11)             | NO   |     | NULL    |       |
// | public       | tinyint(1)          | NO   |     | 0       |       |
// | visible      | tinyint(1)          | NO   |     | 1       |       |
// | downloadable | tinyint(1)          | NO   |     | 0       |       |
// | password     | varchar(100)        | YES  |     | NULL    |       |
// +--------------+---------------------+------+-----+---------+-------+

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('albums');
		Schema::create('albums', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('title', 100)->default('');
			$table->integer('owner_id')->default(0);
			$table->bigInteger('parent_id')->unsigned()->nullable()->default(null)->index();
			$table->foreign('parent_id')->references('id')->on('albums');
			$table->text('description');
			$table->timestamp('min_takestamp')->nullable();
			$table->timestamp('max_takestamp')->nullable();
			$table->boolean('public')->default(false);
			$table->boolean('full_photo')->default(true);
			$table->boolean('visible_hidden')->default(true);
			$table->boolean('downloadable')->default(false);
			$table->string('password', 100)->nullable()->default(null);
			$table->string('license', 20)->default('none');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('albums');
	}
};
