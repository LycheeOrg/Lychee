<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const RANDOM_ID_LENGTH = 24;
	private const PERSON_ID = 'person_id';
	private const ALBUM_ID = 'album_id';

	public function up(): void
	{
		if (Schema::hasTable('person_albums')) {
			$this->ensureConfigKeys();

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

		$this->ensureConfigKeys();
	}

	private function ensureConfigKeys(): void
	{
		if (DB::table('configs')->where('key', 'PA_override_visibility')->doesntExist()) {
			DB::table('configs')->insert([
				'key' => 'PA_override_visibility',
				'value' => '0',
				'cat' => 'smart-albums',
				'type_range' => 'bool',
				'is_secret' => false,
				'description' => 'When true, Person Albums bypass album-based access control',
				'level' => 0,
			]);
		}
		if (DB::table('configs')->where('key', 'hide_nsfw_in_person_albums')->doesntExist()) {
			DB::table('configs')->insert([
				'key' => 'hide_nsfw_in_person_albums',
				'value' => '1',
				'cat' => 'smart-albums',
				'type_range' => 'bool',
				'is_secret' => false,
				'description' => 'When true, NSFW photos are hidden from Person Albums',
				'level' => 0,
			]);
		}
	}

	public function down(): void
	{
		Schema::dropIfExists('person_albums_persons');
		Schema::dropIfExists('person_albums');
		DB::table('configs')->whereIn('key', ['PA_override_visibility', 'hide_nsfw_in_person_albums'])->delete();
	}
};
