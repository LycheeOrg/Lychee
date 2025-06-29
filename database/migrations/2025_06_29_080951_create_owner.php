<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'owner_id',
				'value' => '',
				'cat' => 'Admin',
				'type_range' => 'admin_user',
				'description' => 'Owner of the installation',
				'details' => '<span class="pi pi-exclamation-triangle text-orange-500"></span> Changing this value will allow another admin to take over the server.',
				'is_secret' => true,
				'is_expert' => true,
				'level' => 0,
				'order' => 11,
			],
		]);

		// Set the owner_id to the first user with may_administrate set to true
		// If no such user exists (installation phase), set it to 0
		$owner = DB::table('users')->select('id')->where('may_administrate', true)->orderBy('id', 'asc')->first();
		if ($owner !== null) {
			DB::table('configs')->where('key', 'owner_id')->update(['value' => $owner->id]);
		} else {
			DB::table('configs')->where('key', 'owner_id')->update(['value' => 0]);
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'owner_id')->delete();
	}
};
