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
	public function up(): void
	{
		Schema::create('contact_messages', function (Blueprint $table): void {
			$table->id();
			$table->string('name', 255);
			$table->string('email', 255);
			$table->text('message');
			$table->boolean('is_read')->default(false);
			$table->string('ip_address', 45)->nullable();
			$table->string('user_agent', 512)->nullable();
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);

			$table->index('created_at');
			$table->index('is_read');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('contact_messages');
	}
};
