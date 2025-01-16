<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'zip_deflate_level')->update([
			'details' => '-1 = disable compression (use STORE method), 0 = no compression (use DEFLATE method), 1 = minimal compression (fast), ... 9 = maximum compression (slow)',
			'description' => 'Zip compression level.',
		]);
		DB::table('configs')->where('key', '=', 'default_album_protection')->update(['description' => 'Default protection for newly created albums']);
		DB::table('configs')->where('key', '=', 'login_button_position')->update(['description' => 'Position of the login button']);

		DB::table('configs')->where('key', '=', 'date_format_photo_thumb')->update(['description' => 'Format the date for the photo thumbs.']);
		DB::table('configs')->where('key', '=', 'date_format_photo_overlay')->update(['description' => 'Format the date for the photo overlay.']);
		DB::table('configs')->where('key', '=', 'date_format_sidebar_uploaded')->update(['description' => 'Format the upload date for the photo sidebar.']);
		DB::table('configs')->where('key', '=', 'date_format_sidebar_taken_at')->update(['description' => 'Format the capture date for the photo sidebar.']);
		DB::table('configs')->where('key', '=', 'date_format_hero_min_max')->update(['description' => 'Format the date for the album hero.']);
		DB::table('configs')->where('key', '=', 'date_format_hero_created_at')->update(['description' => 'Format the created date for the album details.']);
		DB::table('configs')->where('key', '=', 'date_format_album_thumb')->update(['description' => 'Format the date for the album thumbs.']);
		DB::table('configs')->whereIn('key', [
			'date_format_photo_thumb',
			'date_format_photo_overlay',
			'date_format_sidebar_uploaded',
			'date_format_sidebar_taken_at',
			'date_format_hero_min_max',
			'date_format_hero_created_at',
			'date_format_album_thumb',
		])->update(['details' => 'See https://www.php.net/manual/en/datetime.format.php']);
		DB::table('configs')->where('key', '=', 'display_thumb_album_overlay')->update(['description' => 'Display the title and metadata on album thumbs']);
		DB::table('configs')->where('key', '=', 'display_thumb_photo_overlay')->update(['description' => 'Display the title and metadata on photo thumbs']);
		DB::table('configs')->where('key', '=', 'default_album_thumb_aspect_ratio')->update(['description' => 'Default aspect ratio for album thumbs']);
		DB::table('configs')->where('key', '=', 'use_album_compact_header')->update(['description' => 'Disable the header image in albums']);
		DB::table('configs')->where('key', '=', 'thumb_min_max_order')->update(['description' => 'Set which date to display first in thumb.']);
		DB::table('configs')->where('key', '=', 'header_min_max_order')->update(['description' => 'Set which date to display first in header.']);
		DB::table('configs')->where('key', '=', 'current_job_processing_visible')->update(['description' => 'Make the processing job queue visible by default']);
		DB::table('configs')->where('key', '=', 'local_takestamp_video_formats')->update(['description' => 'Use local takestamp for the following video formats']);
		DB::table('configs')->where('key', '=', 'back_button_enabled')->update(['description' => 'Enable/disable back button on gallery']);

		DB::table('configs')->where('key', '=', 'enable_unsorted')->update(['description' => 'Enable Unsorted smart album.',
			'details' => 'Warning! Disabling this will make pictures without an album invisible.']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'zip_deflate_level')->update([
			'details' => '',
			'description' => 'DEFLATE compression level: -1 = disable compression (use STORE method), 0 = no compression (use DEFLATE method), 1 = minimal compression (fast), ... 9 = maximum compression (slow)',
		]);
		DB::table('configs')->where('key', '=', 'default_album_protection')->update(['description' => 'Default protection for newly created albums. 1 = private, 2 = public, 3 = inherit from parent']);
		DB::table('configs')->where('key', '=', 'login_button_position')->update(['description' => 'Position of the login button (left | right)']);

		DB::table('configs')->where('key', '=', 'date_format_photo_thumb')->update([
			'details' => '',
			'description' => 'Format the date for the photo thumbs. See https://www.php.net/manual/en/datetime.format.php',
		]);
		DB::table('configs')->where('key', '=', 'date_format_photo_overlay')->update([
			'details' => '',
			'description' => 'Format the date for the photo overlay. See https://www.php.net/manual/en/datetime.format.php',
		]);
		DB::table('configs')->where('key', '=', 'date_format_sidebar_uploaded')->update([
			'details' => '',
			'description' => 'Format the upload date for the photo sidebar. See https://www.php.net/manual/en/datetime.format.php',
		]);
		DB::table('configs')->where('key', '=', 'date_format_sidebar_taken_at')->update([
			'details' => '',
			'description' => 'Format the capture date for the photo sidebar. See https://www.php.net/manual/en/datetime.format.php',
		]);
		DB::table('configs')->where('key', '=', 'date_format_hero_min_max')->update([
			'details' => '',
			'description' => 'Format the date for the album hero. See https://www.php.net/manual/en/datetime.format.php',
		]);
		DB::table('configs')->where('key', '=', 'date_format_hero_created_at')->update([
			'details' => '',
			'description' => 'Format the created date for the album details. See https://www.php.net/manual/en/datetime.format.php',
		]);
		DB::table('configs')->where('key', '=', 'date_format_album_thumb')->update([
			'details' => '',
			'description' => 'Format the date for the album thumbs. See https://www.php.net/manual/en/datetime.format.php',
		]);
		DB::table('configs')->where('key', '=', 'display_thumb_album_overlay')->update(['description' => 'Display the title and metadata on album thumbs (always|hover|never)']);
		DB::table('configs')->where('key', '=', 'display_thumb_photo_overlay')->update(['description' => 'Display the title and metadata on photo thumbs (always|hover|never)']);
		DB::table('configs')->where('key', '=', 'default_album_thumb_aspect_ratio')->update(['description' => 'Default aspect ratio for album thumbs, one of: 1/1, 2/3, 3/2, 4/5, 5/4, 16/9']);
		DB::table('configs')->where('key', '=', 'use_album_compact_header')->update(['description' => 'Disable the header image in albums (0|1)']);
		DB::table('configs')->where('key', '=', 'thumb_min_max_order')->update(['description' => 'Set which date to display first in thumb. Allowed values: older_younger, younger_older']);
		DB::table('configs')->where('key', '=', 'header_min_max_order')->update(['description' => 'Set which date to display first in header. Allowed values: older_younger, younger_older']);
		DB::table('configs')->where('key', '=', 'current_job_processing_visible')->update(['description' => 'Make the processing job queue visible by default (0|1).']);
		DB::table('configs')->where('key', '=', 'back_button_enabled')->update(['description' => 'Enable/disable back button on gallery (0 | 1)']);
		DB::table('configs')->where('key', '=', 'enable_unsorted')->update(['description' => 'Enable Unsorted smart album. Warning! Disabling this will make pictures without an album invisible.']);
	}
};
