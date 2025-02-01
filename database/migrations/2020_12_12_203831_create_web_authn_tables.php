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
	public const DELETED_AT = 'disabled_at';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('web_authn_credentials', function (Blueprint $table) {
			$table->string('id', 255);

			// Change accordingly for your users table if you need to.
			$table->unsignedBigInteger('user_id');

			$table->string('name')->nullable();
			$table->string('type', 16);
			$table->json('transports');
			$table->json('attestation_type');
			$table->json('trust_path');
			$table->uuid('aaguid');
			$table->binary('public_key');
			$table->unsignedInteger('counter')->default(0);

			// This saves the external "ID" that identifies the user. We use UUID default
			// since it's very straightforward. You can change this for a plain string.
			// It must be nullable because those old U2F keys do not use user handle.
			$table->uuid('user_handle')->nullable();

			$table->timestamps();
			$table->softDeletes(self::DELETED_AT);
			DB::table('configs')->where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['type_range' => 'string']);

			$table->primary(['id', 'user_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (Schema::hasTable('configs')) {
			DB::table('configs')->where('key', '=', 'username')->orWhere('key', '=', 'password')->update(['type_range' => 'string_required']);
		}
		Schema::dropIfExists('web_authn_credentials');
	}
};
