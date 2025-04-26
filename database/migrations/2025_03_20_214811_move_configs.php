<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	public const CAT = 'cat';
	public const ORDER = 'order';
	public const MAX_ORDER = 32767;

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
		// Configs
		DB::table('configs')->where('key', 'dark_mode_enabled')->update([self::ORDER => 0, 'description' => 'Use dark mode for Lychee']);
		DB::table('configs')->where('key', 'site_owner')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'site_title')->update([self::ORDER => 2]);

		DB::table('configs')->where('key', 'default_album_protection')->update([self::CAT => self::GALLERY]);
		DB::table('configs')->where('key', 'grants_download')->update([self::CAT => self::GALLERY]);
		DB::table('configs')->where('key', 'legacy_id_redirection')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'force_32bit_ids')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'show_keybinding_help_button')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'show_keybinding_help_popup')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'zip_deflate_level')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'zip64')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'hide_version_number')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'share_button_visible')->update([self::CAT => self::GALLERY]);
		DB::table('configs')->where('key', 'site_copyright_begin')->update([self::CAT => self::FOOTER]);
		DB::table('configs')->where('key', 'site_copyright_end')->update([self::CAT => self::FOOTER]);
		DB::table('configs')->where('key', 'raw_formats')->update([self::CAT => 'Image Processing']);
		DB::table('configs')->where('key', 'update_check_every_days')->update([self::CAT => self::ADMIN]);

		// Lychee SE
		DB::table('configs')->where('key', 'disable_se_call_for_actions')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'enable_se_preview')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'license_key')->update([self::ORDER => 2]);

		// Gallery
		DB::table('configs')->where('key', 'layout')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'default_album_thumb_aspect_ratio')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'sorting_albums_col')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'sorting_albums_order')->update([self::ORDER => 3]);
		DB::table('configs')->where('key', 'sorting_photos_col')->update([self::ORDER => 4]);
		DB::table('configs')->where('key', 'sorting_photos_order')->update([self::ORDER => 5]);

		DB::table('configs')->where('key', 'album_decoration')->update([self::ORDER => 6]);
		DB::table('configs')->where('key', 'album_decoration_orientation')->update([self::ORDER => 7]);
		DB::table('configs')->where('key', 'album_subtitle_type')->update([self::ORDER => 8]);
		DB::table('configs')->where('key', 'image_overlay_type')->update([self::ORDER => 9]);
		DB::table('configs')->where('key', 'display_thumb_album_overlay')->update([self::ORDER => 10]);
		DB::table('configs')->where('key', 'display_thumb_photo_overlay')->update([self::ORDER => 11]);

		DB::table('configs')->where('key', 'thumb_min_max_order')->update([self::ORDER => 12]);
		DB::table('configs')->where('key', 'header_min_max_order')->update([self::ORDER => 13]);
		DB::table('configs')->where('key', 'use_album_compact_header')->update([self::ORDER => 14]);

		DB::table('configs')->where('key', 'autoplay_enabled')->update([self::ORDER => 15]);
		DB::table('configs')->where('key', 'photos_wraparound')->update([self::ORDER => 16]);
		DB::table('configs')->where('key', 'slideshow_timeout')->update([self::ORDER => 17]);
		DB::table('configs')->where('key', 'default_license')->update([self::ORDER => 18]);
		DB::table('configs')->where('key', 'default_album_protection')->update([self::ORDER => 19]);
		DB::table('configs')->where('key', 'grants_download')->update([self::ORDER => 20]);
		DB::table('configs')->where('key', 'grants_full_photo_access')->update([self::ORDER => 21]);
		DB::table('configs')->where('key', 'share_button_visible')->update([self::ORDER => 22]);
		DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update([self::ORDER => 23]);
		DB::table('configs')->where('key', 'login_required')->update([self::ORDER => 24]);
		DB::table('configs')->where('key', 'login_required_root_only')->update([self::ORDER => 25]);
		DB::table('configs')->where('key', 'number_albums_per_row_mobile')->update([self::ORDER => 26]);
		DB::table('configs')->where('key', 'low_number_of_shoots_per_day')->update([self::ORDER => 27]);
		DB::table('configs')->where('key', 'medium_number_of_shoots_per_day')->update([self::ORDER => 28]);
		DB::table('configs')->where('key', 'high_number_of_shoots_per_day')->update([self::ORDER => 29]);
		DB::table('configs')->where('key', 'photo_layout_gap')->update([self::ORDER => 30]);
		DB::table('configs')->where('key', 'photo_layout_grid_column_width')->update([self::ORDER => 31]);
		DB::table('configs')->where('key', 'photo_layout_justified_row_height')->update([self::ORDER => 32]);
		DB::table('configs')->where('key', 'photo_layout_masonry_column_width')->update([self::ORDER => 33]);
		DB::table('configs')->where('key', 'photo_layout_square_column_width')->update([self::ORDER => 34]);

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

		// Search
		DB::table('configs')->where('key', 'hide_nsfw_in_search')->update([self::CAT => self::MOD_NSFW]);
		DB::table('configs')->where('key', 'search_public')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'search_minimum_length_required')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'search_pagination_limit')->update([self::ORDER => 2]);

		// Frame
		DB::table('configs')->where('key', 'hide_nsfw_in_frame')->update([self::CAT => self::MOD_NSFW]);
		DB::table('configs')->where('key', 'mod_frame_enabled')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'random_album_id')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'mod_frame_refresh')->update([self::ORDER => 2]);

		// Map/GPS
		DB::table('configs')->where('key', 'hide_nsfw_in_map')->update([self::CAT => self::MOD_NSFW]);
		DB::table('configs')->where('key', 'map_display')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'map_display_public')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'map_display_direction')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'map_include_subalbums')->update([self::ORDER => 3]);
		DB::table('configs')->where('key', 'map_provider')->update([self::ORDER => 4]);
		DB::table('configs')->where('key', 'location_decoding')->update([self::ORDER => 5]);
		DB::table('configs')->where('key', 'location_decoding_timeout')->update([self::ORDER => 6]);
		DB::table('configs')->where('key', 'location_show')->update([self::ORDER => 7]);
		DB::table('configs')->where('key', 'location_show_public')->update([self::ORDER => 8]);

		// RSS
		DB::table('configs')->where('key', 'hide_nsfw_in_rss')->update([self::CAT => self::MOD_NSFW]);
		DB::table('configs')->where('key', 'rss_enable')->update([self::ORDER => 0]);

		// Sensitive
		DB::table('configs')->where('key', 'nsfw_visible')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'nsfw_warning')->update([self::ORDER => 1, 'details' => 'Display a warning when the album is opened the first time.']);
		DB::table('configs')->where('key', 'nsfw_blur')->update([self::ORDER => 2, 'details' => 'Blur the album cover.']);
		DB::table('configs')->where('key', 'nsfw_banner_blur_backdrop')->update([self::ORDER => 3, 'details' => 'Blur the backdrop of the warning.']);
		DB::table('configs')->where('key', 'nsfw_banner_override')->update([self::ORDER => 4]);
		DB::table('configs')->where('key', 'nsfw_warning_admin')->update([self::ORDER => 5]);

		// Back home
		DB::table('configs')->where('key', 'back_button_enabled')->update([self::ORDER => 0]);

		// Cache
		DB::table('configs')->where('key', 'cache_enabled')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'cache_ttl')->update([self::ORDER => 1]);

		// Symbolic Link
		DB::table('configs')->where('key', 'SL_enable')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'SL_life_time_days')->update([self::ORDER => 1]);

		// User management
		DB::table('configs')->where('key', 'allow_username_change')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'default_user_quota')->update([self::ORDER => 1]);

		// Admin
		DB::table('configs')->where('key', 'lang')->update([self::CAT => self::CONFIG, self::ORDER => 3]);
		DB::table('configs')->where('key', 'dropbox_key')->update([self::ORDER => 0]);
		DB::table('configs')->where('key', 'check_for_updates')->update([self::ORDER => 1]);
		DB::table('configs')->where('key', 'allow_online_git_pull')->update([self::ORDER => 2]);
		DB::table('configs')->where('key', 'apply_composer_update')->update([self::ORDER => 3]);
		DB::table('configs')->where('key', 'force_migration_in_production')->update([self::ORDER => 4]);
		DB::table('configs')->where('key', 'force_32bit_ids')->update([self::ORDER => 5]);
		DB::table('configs')->where('key', 'legacy_id_redirection')->update([self::ORDER => 6]);
		DB::table('configs')->where('key', 'show_keybinding_help_button')->update([self::ORDER => 7]);
		DB::table('configs')->where('key', 'show_keybinding_help_popup')->update([self::ORDER => 8]);
		DB::table('configs')->where('key', 'version')->update([self::ORDER => 9]);
		DB::table('configs')->where('key', 'hide_version_number')->update([self::ORDER => 10]);

		DB::table('configs')->where('key', 'sm_twitter_url')->update(['description' => 'Url of X profile (formerly Twitter)']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'default_album_protection')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'grants_download')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'legacy_id_redirection')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'force_32bit_ids')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'show_keybinding_help_button')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'show_keybinding_help_popup')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'zip_deflate_level')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'zip64')->update([self::CAT => self::CONFIG]);

		DB::table('configs')->where('key', 'lang')->update([self::CAT => self::ADMIN]);
		DB::table('configs')->where('key', 'raw_formats')->update([self::CAT => self::CONFIG]);

		DB::table('configs')->where('key', 'site_copyright_begin')->update([self::CAT => self::CONFIG]);
		DB::table('configs')->where('key', 'site_copyright_end')->update([self::CAT => self::CONFIG]);

		DB::table('configs')->where('key', 'hide_version_number')->update([self::CAT => self::CONFIG]);

		DB::table('configs')->where('key', 'hide_nsfw_in_smart_albums')->update([self::CAT => self::SMART_ALBUMS]);
		DB::table('configs')->where('key', 'hide_nsfw_in_frame')->update([self::CAT => 'Mod Frame']);
		DB::table('configs')->where('key', 'hide_nsfw_in_search')->update([self::CAT => 'Mod Search']);
		DB::table('configs')->where('key', 'hide_nsfw_in_rss')->update([self::CAT => 'Mod RSS']);

		DB::table('configs')->whereIn('key', ['nsfw_warning', 'nsfw_blur', 'nsfw_banner_blur_backdrop'])->update(['details' => '']);

		DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update([self::CAT => self::SMART_ALBUMS]);
		DB::table('configs')->where('key', 'sm_twitter_url')->update(['description' => 'Url of twitter profile']);

		DB::table('configs')->update([self::ORDER => self::MAX_ORDER]);
	}
};
