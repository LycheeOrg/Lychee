<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$ids = DB::table('photos')->leftJoin('statistics', 'photos.id', '=', 'statistics.photo_id')
			->whereNull('statistics.photo_id')->select('photos.id')->pluck('id');

		if ($ids->isEmpty()) {
			return;
		}

		DB::table('statistics')->insert($ids->map(fn ($id) => ['photo_id' => $id])->all());
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Nothing to do here.
	}
};
