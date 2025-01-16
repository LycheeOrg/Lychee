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
	public const TABLE_SV = 'size_variants';
	public const COLUMN = 'storage_disk';
	public const DEFAULT = 'images';

	public function up(): void
	{
		if (!Schema::hasColumn(self::TABLE_SV, self::COLUMN)) {
			Schema::table(self::TABLE_SV, function (Blueprint $table) {
				$table->string(self::COLUMN)->default(self::DEFAULT);
			});
		}
	}

	public function down(): void
	{
		Schema::table(self::TABLE_SV, function (Blueprint $table) {
			$table->dropColumn(self::COLUMN);
		});
	}
};
