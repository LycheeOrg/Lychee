<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Enum\StorageDiskType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	public const RANDOM_ID_LENGTH = 24;
	// Schema-level column defaults cannot reference a PHP enum; this literal must stay in sync with StorageDiskType::LOCAL->value.
	public const DEFAULT_DISK = 'images';

	public function up(): void
	{
		Schema::create('tracks', function (Blueprint $table): void {
			$table->bigIncrements('id');
			$table->char('album_id', self::RANDOM_ID_LENGTH)->nullable(false);
			$table->string('name')->nullable(false);
			$table->string('file_name')->nullable(false);
			$table->string('disk')->nullable(false)->default(self::DEFAULT_DISK);
			$table->boolean('is_primary')->nullable(false)->default(false);
			$table->timestamps();

			$table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
		});

		$this->backfill();

		Schema::table('albums', function (Blueprint $table): void {
			$table->dropColumn('track_short_path');
		});
	}

	/**
	 * Reversible per NFR-055-04, with an accepted caveat: this is only safe to
	 * run shortly after `up()`, before multi-track data (v8) has accumulated.
	 * A rollback after that point silently discards every non-primary track.
	 */
	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table): void {
			$table->string('track_short_path')->nullable();
		});

		DB::table('tracks')
			->where('is_primary', '=', true)
			->get(['album_id', 'file_name'])
			->each(function (object $track): void {
				DB::table('albums')
					->where('id', '=', $track->album_id)
					->update(['track_short_path' => $track->file_name]);
			});

		Schema::dropIfExists('tracks');
	}

	private function backfill(): void
	{
		$now = now();

		DB::table('albums')
			->whereNotNull('track_short_path')
			->orderBy('id')
			->get(['id', 'track_short_path'])
			->each(function (object $album) use ($now): void {
				DB::table('tracks')->insert([
					'album_id' => $album->id,
					'name' => pathinfo($album->track_short_path, PATHINFO_FILENAME),
					'file_name' => $album->track_short_path,
					'disk' => StorageDiskType::LOCAL->value,
					'is_primary' => true,
					'created_at' => $now,
					'updated_at' => $now,
				]);
			});
	}
};
