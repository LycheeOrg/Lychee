<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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
		Schema::dropIfExists('purchasable_prices'); // Reset in case of an error during development
		Schema::dropIfExists('purchasables'); // Reset in case of an error during development

		Schema::create('purchasables', function (Blueprint $table) {
			$table->id();
			$table->char(self::ALBUM_ID, self::RANDOM_ID_LENGTH)->nullable(true);
			$table->char(self::PHOTO_ID, self::RANDOM_ID_LENGTH)->nullable(true);
			$table->text('description')->nullable(true)->comment('Public description shown to customers');
			$table->text('owner_notes')->nullable(true)->comment('Private notes for the owner');
			$table->boolean('is_active')->nullable(false)->default(true);
			$table->timestamps();

			// Create a unique constraint to prevent duplicates
			$table->unique([self::ALBUM_ID, self::PHOTO_ID], 'unique_purchasable');

			// Foreign keys
			$table->foreign(self::ALBUM_ID)->references('id')->on('albums')->onDelete('cascade');
			$table->foreign(self::PHOTO_ID)->references('id')->on('photos')->onDelete('cascade');
		});

		// Create the pricing table to handle the license type × size variant combinations
		Schema::create('purchasable_prices', function (Blueprint $table) {
			$table->id();
			$table->foreignId('purchasable_id')->constrained()->onDelete('cascade');
			$table->string('size_variant', 50)->comment('MEDIUM, FULL, ORIGINAL');
			$table->string('license_type', 50)->comment('PERSONAL, COMMERCIAL, EXTENDED');
			$table->integer('price_cents')->nullable(false)->comment('Price in cents');
			$table->timestamps();

			// Ensure we can't have duplicate price entries
			$table->unique(['purchasable_id', 'size_variant', 'license_type'], 'unique_pricing');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('purchasable_prices');
		Schema::dropIfExists('purchasables');
	}
};
