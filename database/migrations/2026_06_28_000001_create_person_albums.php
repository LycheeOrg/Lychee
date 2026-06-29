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
	private const PERSON_ID = 'person_id';
	private const ALBUM_ID = 'album_id';

	public function up(): void
	{
		if (Schema::hasTable('person_albums')) {
			return;
		}

		Schema::create('person_albums', function (Blueprint $table) {
			$table->char('id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->boolean('is_and')->nullable(false)->default(false);
			$table->primary('id');
			$table->foreign('id')->references('id')->on('base_albums')->cascadeOnUpdate()->cascadeOnDelete();
		});

		Schema::create('person_albums_persons', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->char(self::PERSON_ID, self::RANDOM_ID_LENGTH)->nullable(false);
			$table->char(self::ALBUM_ID, self::RANDOM_ID_LENGTH)->nullable(false);

			$table->index([self::PERSON_ID]);
			$table->index([self::ALBUM_ID]);
			$table->unique([self::PERSON_ID, self::ALBUM_ID]);
			$table->foreign(self::PERSON_ID)->references('id')->on('persons')->cascadeOnDelete();
			$table->foreign(self::ALBUM_ID)->references('id')->on('person_albums')->cascadeOnUpdate()->cascadeOnDelete();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('person_albums_persons');
		Schema::dropIfExists('person_albums');
	}
};
