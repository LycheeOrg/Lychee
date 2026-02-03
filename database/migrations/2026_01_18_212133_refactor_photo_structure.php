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

require_once 'TemporaryModels/PhotoVideo.php';

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (!Schema::hasColumn('photos', 'duration')) {
			Schema::table('photos', function (Blueprint $table) {
				$table->string('duration')->nullable()->after('focal');
				$table->string('fps')->nullable()->after('duration');
			});
		}

		DB::transaction(function () {
			DB::table('photos')->select('id', 'aperture', 'focal')
				->where('type', 'LIKE', 'video/%')
				->chunkById(100, function ($photos) {
					$update = $photos->map(fn ($photo) => [
						'id' => $photo->id,
						'duration' => $photo->aperture === null ? null : (string) $photo->aperture,
						'fps' => $photo->focal === null ? null : (string) $photo->focal,
					])->all();
					$key_name = 'id';
					$photo_instance = new PhotoVideo();
					batch()->update($photo_instance, $update, $key_name);
				}, 'id');

			DB::table('photos')
				->where('type', 'LIKE', 'video/%')
				->update(['aperture' => null, 'focal' => null]);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::transaction(function () {
			DB::table('photos')->select('id', 'duration', 'fps')
				->where('type', 'LIKE', 'video/%')
				->chunkById(100, function ($photos) {
					$update = $photos->map(fn ($photo) => [
						'id' => $photo->id,
						'aperture' => $photo->duration === null ? null : (string) $photo->duration,
						'focal' => $photo->fps === null ? null : (string) $photo->fps,
					])->all();
					$key_name = 'id';
					$photo_instance = new PhotoVideo();
					batch()->update($photo_instance, $update, $key_name);
				}, 'id');
		});

		Schema::table('photos', function (Blueprint $table) {
			$table->dropColumn(['duration', 'fps']);
		});
	}
};
