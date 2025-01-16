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
		DB::table('configs')->where('key', '=', 'enable_unsorted')->update(['details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Disabling this smart album will make pictures without an album invisible.']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'enable_unsorted')->update(['details' => 'Warning! Disabling this will make pictures without an album invisible.']);
	}
};