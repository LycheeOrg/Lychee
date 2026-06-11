<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
		Schema::table('orders', function (Blueprint $table) {
			$table->string('shipping_street_name')->nullable()->after('comment');
			$table->string('shipping_street_number')->nullable()->after('shipping_street_name');
			$table->string('shipping_additional_info')->nullable()->after('shipping_street_number');
			$table->string('shipping_city')->nullable()->after('shipping_additional_info');
			$table->string('shipping_post_code')->nullable()->after('shipping_city');
			$table->string('shipping_country')->nullable()->after('shipping_post_code');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('orders', function (Blueprint $table) {
			$table->dropColumn([
				'shipping_street_name',
				'shipping_street_number',
				'shipping_additional_info',
				'shipping_city',
				'shipping_post_code',
				'shipping_country',
			]);
		});
	}
};
