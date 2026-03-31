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
		Schema::create('webhooks', function (Blueprint $table): void {
			$table->ulid('id')->primary();
			$table->string('name', 255);
			$table->string('event', 50); // PhotoWebhookEvent: photo.add | photo.move | photo.delete
			$table->string('method', 10); // WebhookMethod: GET | POST | PUT | PATCH | DELETE
			$table->string('url', 2048);
			$table->string('payload_format', 20)->default('json'); // WebhookPayloadFormat: json | query_string
			$table->text('secret')->nullable(); // stored encrypted
			$table->string('secret_header', 255)->nullable(); // defaults to X-Webhook-Secret at dispatch time
			$table->boolean('enabled')->default(true);
			$table->boolean('send_photo_id')->default(true);
			$table->boolean('send_album_id')->default(true);
			$table->boolean('send_title')->default(true);
			$table->boolean('send_size_variants')->default(false);
			$table->json('size_variant_types')->nullable(); // array of SizeVariantType enum integer values
			$table->dateTime('created_at', 6)->nullable(false);
			$table->dateTime('updated_at', 6)->nullable(false);

			$table->index('event');
			$table->index('enabled');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('webhooks');
	}
};
