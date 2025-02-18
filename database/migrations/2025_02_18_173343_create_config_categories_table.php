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
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('config_categories', function (Blueprint $table) {
			$table->string('cat');
			$table->string('name');
			$table->string('description')->default('');
			$table->unsignedTinyInteger('order')->default(255);

			$table->primary('cat');
			$table->unique('name');
		});

		DB::table('config_categories')->insert([
			['cat' => 'config', 'name' => 'Basics', 'description' => '', 'order' => 0],
			['cat' => 'lychee SE', 'name' => 'Lychee SE', 'description' => '', 'order' => 1],
			['cat' => 'Gallery', 'name' => 'Gallery', 'description' => '', 'order' => 2],
			['cat' => 'Mod Welcome', 'name' => 'Landing page', 'description' => '', 'order' => 3],
			['cat' => 'Footer', 'name' => 'Footer', 'description' => '', 'order' => 4],
			['cat' => 'Smart Albums', 'name' => '', 'description' => '', 'order' => 5],
			['cat' => 'Image Processing', 'name' => 'Image Processing', 'description' => '', 'order' => 6],
			['cat' => 'Mod Search', 'name' => 'Search Module', 'description' => '', 'order' => 7],
			['cat' => 'Mod Timeline', 'name' => 'Timeline Module', 'description' => '', 'order' => 8],
			['cat' => 'Mod Frame', 'name' => 'Frame Module', 'description' => '', 'order' => 9],
			['cat' => 'Mod Map', 'name' => 'GPS module', 'description' => '', 'order' => 10],
			['cat' => 'Mod RSS', 'name' => 'RSS module', 'description' => '', 'order' => 11],
			['cat' => 'Mod NSFW', 'name' => 'Sensitive Module', 'description' => '', 'order' => 12],
			['cat' => 'Mod Back Button', 'name' => 'Back Home module', 'description' => '', 'order' => 13],
			['cat' => 'Mod Cache', 'name' => 'Cache module', 'description' => '', 'order' => 14],
			['cat' => 'Mod Pro', 'name' => 'Pro Module', 'description' => '', 'order' => 15],
			['cat' => 'Symbolic Link', 'name' => 'Symbolic Link module', 'description' => '', 'order' => 16],
			['cat' => 'Mod Privacy', 'name' => 'Privacy Options', 'description' => '', 'order' => 17],
			['cat' => 'Users Management', 'name' => 'Users Management', 'description' => '', 'order' => 18],
			['cat' => 'Admin', 'name' => 'Admin', 'description' => '', 'order' => 19],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('config_categories');
	}
};
