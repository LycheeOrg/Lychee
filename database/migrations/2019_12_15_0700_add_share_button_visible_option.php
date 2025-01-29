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
		Schema::table('albums', function (Blueprint $table) {
			$table->boolean('share_button_visible')->after('downloadable')->default(false);
		});

		DB::table('albums')
			->where('public', '=', 1)
			->update([
				'share_button_visible' => true,
			]);

		DB::table('configs')->insert([
			'key' => 'share_button_visible',
			'value' => '0',
			'cat' => 'config',
			'type_range' => '0|1',
			'confidentiality' => '0',
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropColumns('albums', ['share_button_visible']);
		DB::table('configs')->where('key', 'share_button_visible')->delete();
	}
};
