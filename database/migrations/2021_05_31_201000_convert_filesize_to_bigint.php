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
	 * Converts the column `filesize` to a 64bit integer such that file sizes >= 4GB can be represented.
	 */
	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->unsignedBigInteger('filesize')->nullable(false)->default(0)->change();
		});
	}

	public function down(): void
	{
		// no-op by intention
	}
};