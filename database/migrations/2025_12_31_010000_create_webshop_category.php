<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {

	const CAT = 'Mod Webshop';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('config_categories')->insert([
			[
				'cat' => self::CAT,
				'name' => 'Webshop',
				'description' => 'This module allows you manage and sell your pictures.<br><br>
				<span class="pi pi-exclamation-triangle text-orange-500 ltr:mr-2 rtl:ml-2"></span>Lychee is developed under the <a href="https://lycheeorg.dev/license" class="text-primary-400">MIT license</a>.
				This means that <span class="text-muted-color-emphasis">LycheeOrg is not responsible</span> nor liable <span class="text-muted-color-emphasis">for any issues or losses</span> arising from the use of the webshop module and/or the payment processing capabilities.
				It is critical that you verify and <span class="text-muted-color-emphasis">ensure that your setup is working correctly and securely before using it in a production environment.',
				'order' => 25,
			],
		]);


		DB::table('configs')->where('key', 'LIKE', 'webshop_%')->update(['cat' => self::CAT]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'LIKE', 'webshop_%')->update(['cat' => 'Mod Pro']);
		DB::table('config_categories')->where('cat', self::CAT)->delete();
	}
};