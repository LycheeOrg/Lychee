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
	public const COL = 'not_on_docker';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('configs', function (Blueprint $table): void {
			$table->boolean(self::COL)->default(false)->after('level')->comment('Defines that this setting is not used/displayed in docker installations');
		});

		DB::table('configs')->whereIn('key', ['allow_online_git_pull', 'apply_composer_update', 'force_migration_in_production'])->update([self::COL => true]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn(self::COL);
		});
	}
};
