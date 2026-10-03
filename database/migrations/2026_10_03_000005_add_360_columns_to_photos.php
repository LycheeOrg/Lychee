<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feature 081: 360° photos.
 *
 * `is_360` is NULL until the photo has been checked (upload or
 * `lychee:detect_360`). The crop columns place a partial photo sphere in its
 * full panorama, in pixels of the original; NULL means full sphere.
 */
return new class() extends Migration {
	public function up(): void
	{
		Schema::table('photos', function (Blueprint $table): void {
			$table->boolean('is_360')->nullable()->default(null)->after('img_direction');
			$table->unsignedInteger('pano_full_width')->nullable()->default(null)->after('is_360');
			$table->unsignedInteger('pano_full_height')->nullable()->default(null)->after('pano_full_width');
			$table->unsignedInteger('pano_crop_left')->nullable()->default(null)->after('pano_full_height');
			$table->unsignedInteger('pano_crop_top')->nullable()->default(null)->after('pano_crop_left');
		});
	}

	public function down(): void
	{
		Schema::table('photos', function (Blueprint $table): void {
			$table->dropColumn(['is_360', 'pano_full_width', 'pano_full_height', 'pano_crop_left', 'pano_crop_top']);
		});
	}
};
