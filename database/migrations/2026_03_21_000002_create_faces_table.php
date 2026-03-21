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

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('faces', function (Blueprint $table) {
			$table->char('id', self::RANDOM_ID_LENGTH)->primary();
			$table->char('photo_id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->char('person_id', self::RANDOM_ID_LENGTH)->nullable(true);
			$table->float('x')->nullable(false);
			$table->float('y')->nullable(false);
			$table->float('width')->nullable(false);
			$table->float('height')->nullable(false);
			$table->float('confidence')->nullable(false);
			$table->string('crop_token')->nullable(true);
			$table->boolean('is_dismissed')->default(false);
			$table->timestamps();

			$table->index('photo_id');
			$table->index('person_id');
			$table->foreign('photo_id')->references('id')->on('photos')->cascadeOnDelete();
			$table->foreign('person_id')->references('id')->on('persons')->nullOnDelete();
		});

		Schema::create('face_suggestions', function (Blueprint $table) {
			$table->char('face_id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->char('suggested_face_id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->float('confidence')->nullable(false);

			$table->unique(['face_id', 'suggested_face_id']);
			$table->foreign('face_id')->references('id')->on('faces')->cascadeOnDelete();
			$table->foreign('suggested_face_id')->references('id')->on('faces')->cascadeOnDelete();
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->string('face_scan_status', 16)->nullable(true)->after('is_highlighted');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn('face_scan_status');
		});

		Schema::dropIfExists('face_suggestions');
		Schema::dropIfExists('faces');
	}
};
