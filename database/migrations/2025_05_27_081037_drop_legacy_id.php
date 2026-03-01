<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends Migration {
	public const LEGACY_ID_NAME = 'legacy_id';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		try {
			Schema::table('photos', function (Blueprint $table) {
				$table->dropUnique('photos_legacy_id_unique');
			});
			Schema::table('base_albums', function (Blueprint $table) {
				$table->dropUnique('base_albums_legacy_id_unique');
			});
		} catch (\Throwable $e) {
			// Do nothing
		}

		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn(self::LEGACY_ID_NAME);
		});

		Schema::table('base_albums', function (Blueprint $table) {
			$table->unsignedBigInteger(self::LEGACY_ID_NAME)->nullable(true)->change();
		});

		DB::table('configs')
			->where('key', '=', 'legacy_id_redirection')
			->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Do not re-add the column... if it already exists.
		if (!Schema::hasColumn('base_albums', self::LEGACY_ID_NAME)) {
			Schema::table('base_albums', function (Blueprint $table) {
				$table->unsignedBigInteger('legacy_id')->after('id')->nullable(false);
			});
		}

		Schema::table('photos', function (Blueprint $table) {
			$table->unsignedBigInteger('legacy_id')->after('id')->nullable(false);
		});

		DB::table('configs')
			->insert([
				'key' => 'legacy_id_redirection',
				'value' => '1',
				'cat' => 'Admin',
				'type_range' => '0|1',
				'is_secret' => 0,
				'description' => 'Enables/disables the redirection support for legacy IDs',
				'level' => 0,
				'not_on_docker' => 0,
				'is_expert' => 1,
				'order' => 6,
			]);
	}
};
