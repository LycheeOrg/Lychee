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
		DB::table('configs')->whereIn('key', ['nsfw_banner_override', 'footer_additional_text'])->update(['details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Unsanitized html field.']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->whereIn('key', ['nsfw_banner_override', 'footer_additional_text'])->update(['details' => '']);
	}
};
