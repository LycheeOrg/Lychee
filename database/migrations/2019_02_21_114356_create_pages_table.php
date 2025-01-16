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

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('pages');
		Schema::create('pages', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title', 150)->default('');
			$table->string('menu_title', 100)->default('');
			$table->boolean('in_menu')->default(false);
			$table->boolean('enabled')->default(false);
			$table->string('link', 150)->default('');
			$table->integer('order')->default(0);
			$table->timestamps();
		});

		DB::table('pages')->insert([
			[
				'title' => 'gallery',
				'menu_title' => 'gallery',
				'in_menu' => true,
				'link' => '/gallery',
				'enabled' => true,
				'order' => 2,
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('pages');
	}
};
