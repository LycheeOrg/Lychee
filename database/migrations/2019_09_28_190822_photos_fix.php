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
	private function fix_thumbs(): void
	{
		// from fix_thumb2x_default
		DB::table('photos')->where('thumbUrl', '=', '')
			->where('thumb2x', '=', '1')
			->update([
				'thumb2x' => '0',
			]);
		Schema::table('photos', function (Blueprint $table) {
			$table->boolean('thumb2x')->default(false)->change();
		});
	}

	private function image_direction(): void
	{
		// migration from imageDirection
		if (!Schema::hasColumn('photos', 'imgDirection')) {
			Schema::table('photos', function (Blueprint $table) {
				$table->decimal('imgDirection', 10, 4)->default(null)
					->after('altitude')->nullable();
			});
		}
	}

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->fix_thumbs();
		$this->image_direction();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
	}
};
