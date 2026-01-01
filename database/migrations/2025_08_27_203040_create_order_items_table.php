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
	private const RANDOM_ID_LENGTH = 24;
	private const ALBUM_ID = 'album_id';
	private const PHOTO_ID = 'photo_id';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('order_items');
		Schema::create('order_items', function (Blueprint $table) {
			$table->id();
			$table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
			$table->foreignId('purchasable_id')->nullable()->constrained()->onDelete('set null');
			$table->char(self::ALBUM_ID, self::RANDOM_ID_LENGTH)->nullable(true);
			$table->char(self::PHOTO_ID, self::RANDOM_ID_LENGTH)->nullable(true);
			$table->string('title', 191)->nullable(false)->comment('Photo or album title at time of purchase');
			$table->string('license_type', 50)->nullable(false);
			$table->integer('price_cents')->nullable(false)->comment('Price in cents');
			$table->string('size_variant_type', 50)->nullable(true)->comment('Only applicable for photos, not albums');
			$table->text('item_notes')->nullable(true);

			// Foreign keys (with set null to maintain order history if photos/albums are deleted)
			$table->foreign(self::ALBUM_ID)->references('id')->on('albums')->onDelete('set null');
			$table->foreign(self::PHOTO_ID)->references('id')->on('photos')->onDelete('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('order_items');
	}
};
