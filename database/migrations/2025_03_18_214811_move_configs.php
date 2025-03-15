<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const CAT = 'cat';
	public const ORDER = 'order';
	public const MAX_ORDER = 65535;

	public const CONFIG = 'config';
	public const ADMIN = 'Admin';
	public const GALLERY = 'Gallery';
	public const FOOTER = 'Footer';
	public const SMART_ALBUMS = 'Smart Albums';
	public const MOD_NSFW = 'Mod NSFW';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'dark_mode_enabled')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'site_owner')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'site_title')->update([self::ORDER => 3]);

		DB::table('configs')->where('key', 'force_32bit_ids')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'show_keybinding_help_button')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'show_keybinding_help_popup')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'zip_deflate_level')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'zip64')->update([self::CAT => self::ADMIN]);

		DB::table('configs')->where('key', 'site_copyright_begin')->update([self::CAT => self::FOOTER]);
		DB::table('configs')->where('key', 'site_copyright_end')->update([self::CAT => self::FOOTER]);

		DB::table('configs')->where('key', 'hide_version_number')->update([self::CAT => self::ADMIN]);

		// Mod Welcome
		DB::table('configs')->where('key', 'landing_page_enable')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'landing_title')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'landing_subtitle')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'landing_background')->update([self::ORDER => 3]);

		// Footer
		DB::table('configs')->where('key', 'footer_show_copyright')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'site_copyright_begin')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'site_copyright_end')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'footer_additional_text')->update([self::ORDER => 3]);
		DB::table('configs')->where('key', 'footer_show_social_media')->update([self::ORDER => 4]);

		// Smart albums
		DB::table('configs')->where('key', 'enable_unsorted')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'enable_starred')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'enable_recent')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'enable_on_this_day')->update([self::ORDER => 3]);

		DB::table('configs')->where('key', 'hide_nsfw_in_smart_albums')->update([self::CAT => self::MOD_NSFW]);
		DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update([self::CAT => self::GALLERY]);

		// Image Processing
		DB::table('configs')->where('key', 'thumb_2x')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'small_max_height')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'small_max_width')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'small_2x')->update([self::ORDER => 3]);
		DB::table('configs')->where('key', 'medium_max_height')->update([self::ORDER => 4]);
		DB::table('configs')->where('key', 'medium_max_width')->update([self::ORDER => 5]);
		DB::table('configs')->where('key', 'medium_2x')->update([self::ORDER => 6]);
		DB::table('configs')->where('key', 'low_quality_image_placeholder')->update([self::ORDER => 7]);
		DB::table('configs')->where('key', 'keep_original_untouched')->update([self::ORDER => 8]);
		DB::table('configs')->where('key', 'auto_fix_orientation')->update([self::ORDER => 9]);
		DB::table('configs')->where('key', 'compression_quality')->update([self::ORDER => 10]);
		DB::table('configs')->where('key', 'delete_imported')->update([self::ORDER => 11]);
		DB::table('configs')->where('key', 'import_via_symlink')->update([self::ORDER => 12]);
		DB::table('configs')->where('key', 'skip_duplicates')->update([self::ORDER => 13]);
		DB::table('configs')->where('key', 'editor_enabled')->update([self::ORDER => 14]);
		DB::table('configs')->where('key', 'upload_chunk_size')->update([self::ORDER => 15]);
		DB::table('configs')->where('key', 'upload_processing_limit')->update([self::ORDER => 16]);
		DB::table('configs')->where('key', 'use_job_queues')->update([self::ORDER => 17]);

		// DB::table('configs')->where('key', 'lang')->update([self::CAT => 'config', self::ORDER => 1]);

		// DB::table('configs')->whereIn('key',
		// 	[
		// 		'force_32bit_ids',
		// 		'zip_deflate_level',
		// 		'zip64',
		// 		'date_format_album_thumb',
		// 		'date_format_hero_created_at',
		// 		'date_format_hero_min_max',
		// 		'date_format_photo_overlay',
		// 		'date_format_photo_thumb',
		// 		'date_format_sidebar_taken_at',
		// 		'date_format_sidebar_uploaded',
		// 		'photo_layout_gap',
		// 		'photo_layout_grid_column_width',
		// 		'photo_layout_justified_row_height',
		// 		'photo_layout_masonry_column_width',
		// 		'photo_layout_square_column_width',
		// 		'prefer_available_xmp_metadata',
		// 		'timeline_album_date_format_day',
		// 		'timeline_album_date_format_month',
		// 		'timeline_album_date_format_year',
		// 		'timeline_photo_date_format_day',
		// 		'timeline_photo_date_format_hour',
		// 		'timeline_photo_date_format_month',
		// 		'timeline_photo_date_format_year',
		// 		'location_decoding_timeout',
		// 		'nsfw_banner_override',
		// 		'cache_event_logging',
		// 		'SL_for_admin',
		// 		'disable_recursive_permission_check',
		// 		'allow_online_git_pull',
		// 		'apply_composer_update',
		// 		'force_migration_in_production',
		// 		'log_max_num_line',
		// 		'maintenance_processing_limit',
		// 		'legacy_id_redirection',
		// 		'show_keybinding_help_button',
		// 		'show_keybinding_help_popup',
		// 		'high_number_of_shoots_per_day',
		// 		'low_number_of_shoots_per_day',
		// 		'medium_number_of_shoots_per_day',
		// 		'footer_additional_text',
		// 		'unlock_password_photos_with_url_param',
		// 		'SA_random_thumbs',
		// 		'exiftool_path',
		// 		'ffmpeg_path',
		// 		'ffprobe_path',
		// 		'has_exiftool',
		// 		'has_ffmpeg',
		// 		'imagick',
		// 		'lossless_optimization',
		// 		'upload_chunk_size',
		// 		'upload_processing_limit',
		// 		'use_job_queues',
		// 		'use_last_modified_date_when_no_exif_date',
		// 		'version'
		// 	])->update([self::COL => true]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'dark_mode_enabled')->update([self::ORDER => self::MAX_ORDER]);
		DB::table('configs')->where('key', 'site_owner')->update([self::ORDER => self::MAX_ORDER]);
		DB::table('configs')->where('key', 'site_title')->update([self::ORDER => self::MAX_ORDER]);

		DB::table('configs')->where('key', 'force_32bit_ids')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'show_keybinding_help_button')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'show_keybinding_help_popup')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'zip_deflate_level')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'zip64')->update([self::CAT => self::CONFIG]);

		DB::table('configs')->where('key', 'site_copyright_begin')->update([self::CAT => self::CONFIG, self::ORDER => self::MAX_ORDER]);
		DB::table('configs')->where('key', 'site_copyright_end')->update([self::CAT => self::CONFIG, self::ORDER => self::MAX_ORDER]);

		DB::table('configs')->where('key', 'hide_version_number')->update([self::CAT => self::CONFIG]);

		DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update([self::CAT => self::SMART_ALBUMS]);

		DB::table('configs')->update([self::ORDER => self::MAX_ORDER]);

		// DB::table('configs')->where('key', 'lang')->update([self::CAT => 'config', self::ORDER => self::MAX_ORDER]);
	}
};
