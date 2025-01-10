<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
	private const TABLE_NAME = 'configs';
	private const NEW_COL_NAME = 'is_secret';
	private const OLD_COL_NAME = 'confidentiality';

	private const CONF_0 = [
		'version',
		'check_for_updates',
		'sorting_albums_col',
		'sorting_albums_order',
		'lang',
		'grants_full_photo_access',
		'mod_frame_enabled',
		'mod_frame_refresh',
		'image_overlay_type',
		'landing_page_enable',
		'site_owner',
		'sm_facebook_url',
		'sm_flickr_url',
		'sm_twitter_url',
		'sm_instagram_url',
		'sm_youtube_url',
		'site_title',
		'footer_show_copyright',
		'site_copyright_begin',
		'site_copyright_end',
		'footer_additional_text',
		'footer_show_social_media',
		'search_public',
		'grants_download',
		'photos_wraparound',
		'map_display',
		'zip64',
		'force_32bit_ids',
		'map_display_public',
		'map_provider',
		'map_include_subalbums',
		'share_button_visible',
		'location_decoding',
		'location_decoding_timeout',
		'location_show',
		'location_show_public',
		'rss_enable',
		'rss_recent_days',
		'rss_max_items',
		'editor_enabled',
		'swipe_tolerance_x',
		'swipe_tolerance_y',
		'nsfw_visible',
		'nsfw_blur',
		'nsfw_warning',
		'nsfw_warning_admin',
		'nsfw_banner_override',
		'map_display_direction',
		'album_subtitle_type',
		'upload_processing_limit',
		'new_photos_notification',
		'legacy_id_redirection',
		'zip_deflate_level',
		'default_album_protection',
		'allow_username_change',
		'album_decoration',
		'album_decoration_orientation',
		'auto_fix_orientation',
		'use_job_queues',
		'random_album_id',
		'use_last_modified_date_when_no_exif_date',
		'layout',
		'date_format_photo_thumb',
		'date_format_photo_overlay',
		'date_format_sidebar_uploaded',
		'date_format_sidebar_taken_at',
		'date_format_hero_min_max',
		'date_format_hero_created_at',
		'date_format_album_thumb',
		'upload_chunk_size',
		'nsfw_banner_blur_backdrop',
		'search_pagination_limit',
		'search_minimum_length_required',
		'photo_layout_justified_row_height',
		'photo_layout_masonry_column_width',
		'photo_layout_grid_column_width',
		'photo_layout_square_column_width',
		'photo_layout_gap',
		'display_thumb_album_overlay',
		'display_thumb_photo_overlay',
		'default_album_thumb_aspect_ratio',
		'use_album_compact_header',
		'login_button_position',
		'back_button_enabled',
		'back_button_text',
		'back_button_url',
		'current_job_processing_visible',
	];
	private const CONF_1 = ['ffmpeg_path', 'ffprobe_path'];
	private const CONF_2 = [
		'sorting_photos_col',
		'sorting_photos_order',
		'imagick',
		'skip_duplicates',
		'default_license',
		'small_max_width',
		'small_max_height',
		'medium_max_width',
		'medium_max_height',
		'delete_imported',
		'compression_quality',
		'landing_title',
		'landing_subtitle',
		'landing_background',
		'thumb_2x',
		'small_2x',
		'medium_2x',
		'recent_age',
		'SL_enable',
		'SL_for_admin',
		'update_check_every_days',
		'has_exiftool',
		'has_ffmpeg',
		'import_via_symlink',
		'prefer_available_xmp_metadata',
		'lossless_optimization',
		'local_takestamp_video_formats',
		'log_max_num_line',
		'unlock_password_photos_with_url_param',
		'SA_enabled',
	];
	private const CONF_3 = [
		'dropbox_key',
		'allow_online_git_pull',
		'force_migration_in_production',
		'hide_version_number',
		'SL_life_time_days',
		'raw_formats',
		'apply_composer_update',
	];

	private const IS_SECRET = [
		'dropbox_key',
		'allow_online_git_pull',
		'apply_composer_update',
		'landing_title',
		'landing_subtitle',
		'landing_background',
		'site_owner',
		'sm_facebook_url',
		'sm_flickr_url',
		'sm_twitter_url',
		'sm_instagram_url',
		'sm_youtube_url',
		'site_title',
		'footer_show_copyright',
		'site_copyright_begin',
		'site_copyright_end',
		'footer_additional_text',
		'footer_show_social_media',
		'SL_life_time_days',
		'raw_formats',
		'ffmpeg_path',
		'ffprobe_path',
		'back_button_url',
	];

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->boolean(self::NEW_COL_NAME)->after(self::OLD_COL_NAME)->default(false);
		});

		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn(self::OLD_COL_NAME);
		});

		DB::table(self::TABLE_NAME)->whereIn('key', self::IS_SECRET)->update([self::NEW_COL_NAME => true]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->tinyInteger(self::OLD_COL_NAME)->after(self::NEW_COL_NAME)->default(0);
		});

		Schema::table(self::TABLE_NAME, function (Blueprint $table) {
			$table->dropColumn(self::NEW_COL_NAME);
		});

		DB::table(self::TABLE_NAME)->whereIn('key', self::CONF_0)->update([self::OLD_COL_NAME => 0]);
		DB::table(self::TABLE_NAME)->whereIn('key', self::CONF_1)->update([self::OLD_COL_NAME => 1]);
		DB::table(self::TABLE_NAME)->whereIn('key', self::CONF_2)->update([self::OLD_COL_NAME => 2]);
		DB::table(self::TABLE_NAME)->whereIn('key', self::CONF_3)->update([self::OLD_COL_NAME => 3]);
	}
};
