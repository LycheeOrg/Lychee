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
		Schema::dropIfExists('orders'); // Reset in case of an error during development

		Schema::create('orders', function (Blueprint $table) {
			$table->id();
			$table->string('transaction_id', 191)->nullable(false)->unique();
			$table->string('provider', 50)->nullable(true)->comment('Payment provider (PayPal, Stripe, etc.)');
			$table->unsignedInteger('user_id')->nullable(true);
			$table->string('email', 191)->nullable(true);
			$table->string('status', 50)->nullable(false)->comment('pending, processing, completed, failed, refunded');
			$table->integer('amount_cents')->nullable(false)->default(0)->comment('Total amount in cents');
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(true);
			$table->dateTime('paid_at', 6)->nullable(true);
			$table->text('comment')->nullable(true);

			// Foreign key constraint
			$table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('orders');
	}
};
