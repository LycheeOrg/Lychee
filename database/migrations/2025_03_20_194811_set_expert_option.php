<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const COL = 'is_expert';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->whereIn('key',
			[
				'zip_deflate_level',
				'zip64',
				'date_format_album_thumb',
				'date_format_hero_created_at',
				'date_format_hero_min_max',
				'date_format_photo_overlay',
				'date_format_photo_thumb',
				'date_format_sidebar_taken_at',
				'date_format_sidebar_uploaded',
				'photo_layout_gap',
				'photo_layout_grid_column_width',
				'photo_layout_justified_row_height',
				'photo_layout_masonry_column_width',
				'photo_layout_square_column_width',
				'prefer_available_xmp_metadata',
				'timeline_album_date_format_day',
				'timeline_album_date_format_month',
				'timeline_album_date_format_year',
				'timeline_photo_date_format_day',
				'timeline_photo_date_format_hour',
				'timeline_photo_date_format_month',
				'timeline_photo_date_format_year',
				'location_decoding_timeout',
				'nsfw_banner_override',
				'cache_event_logging',
				'SL_for_admin',
				'allow_online_git_pull',
				'apply_composer_update',
				'force_migration_in_production',
				'log_max_num_line',
				'legacy_id_redirection',
				'high_number_of_shoots_per_day',
				'low_number_of_shoots_per_day',
				'medium_number_of_shoots_per_day',
				'footer_additional_text',
				'unlock_password_photos_with_url_param',
				'SA_random_thumbs',
				'exiftool_path',
				'ffmpeg_path',
				'ffprobe_path',
				'has_exiftool',
				'has_ffmpeg',
				'imagick',
				'lossless_optimization',
				'upload_chunk_size',
				'upload_processing_limit',
				'use_job_queues',
				'use_last_modified_date_when_no_exif_date',
				'new_photos_notification',
				'version',
				'hide_version_number',
			])->update([self::COL => true]);

		DB::table('configs')->whereIn('key', ['swipe_tolerance_x', 'swipe_tolerance_y', 'current_job_processing_visible', 'job_processing_queue_visible'])->delete();
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->insert([
			[
				'key' => 'swipe_tolerance_x',
				'value' => '1500',
				'cat' => 'Gallery',
				'type_range' => 'positive',
				'description' => 'Defines default horizontal swipe tolerance for mobile interactions',
				'details' => '',
				'order' => 32767,
			],
			[
				'key' => 'swipe_tolerance_y',
				'value' => '250',
				'cat' => 'Gallery',
				'type_range' => 'positive',
				'description' => 'Defines default vertical swipe tolerance for mobile interactions',
				'details' => '',
				'order' => 32767,
			],
			[
				'key' => 'current_job_processing_visible',
				'value' => '1',
				'cat' => 'Image Processing',
				'type_range' => '0|1',
				'description' => 'Make the processing job queue visible by default',
				'details' => '',
				'order' => 32767,
			],
		]);
	}
};
