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

/**
 * Populates `configs.required_keys`.
 *
 * The graph and the reasons behind every edge are documented in
 * docs/specs/3-reference/settings_dependencies.md. Keep both in sync.
 */
return new class() extends Migration {
	public const COL = 'required_keys';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->string(self::COL)->nullable()->after('is_expert');
		});

		// Admin
		$this->requires(['update_check_every_days'], 'check_for_updates');

		// Footer
		$this->requires(['site_copyright_begin', 'site_copyright_end', 'copyright_text'], 'footer_show_copyright');
		$this->requires([
			'sm_facebook_url',
			'sm_flickr_url',
			'sm_instagram_url',
			'sm_twitter_url',
			'sm_youtube_url',
			'sm_pinterest_url',
			'sm_deviantart_url',
			'sm_tumblr_url',
			'sm_500px_url',
			'sm_pixelfeed_url',
			'sm_discord_url',
			'sm_reddit_url',
		], 'footer_show_social_media');

		// Gallery
		$this->requires(['details_links_public'], 'details_links_enabled');
		$this->requires(['slideshow_timeout'], 'slideshow_enabled');
		$this->requires(['photo_ken_burns_on_hover_scale', 'photo_ken_burns_on_hover_duration'], 'photo_ken_burns_on_hover_enabled');
		$this->requires([
			'photo_flags_enabled',
			'album_flags_enabled',
			'smart_album_flags_enabled',
			'public_hidden_flag_enabled',
			'public_visible_flag_enabled',
			'password_flag_enabled',
			'sensitive_flag_enabled',
		], 'flags_enabled');
		$this->requires([
			'cover_id_flag_enabled',
			'header_id_flag_enabled',
			'highlighted_flag_enabled',
			'validated_flag_enabled',
		], 'flags_enabled,photo_flags_enabled');

		// Image Processing
		$this->requires(['keep_original_untouched'], 'auto_fix_orientation');
		$this->requires(['colour_extraction_driver', 'search_colour_distance'], 'enable_colour_extractions');
		$this->requires(['download_archive_chunk_size'], 'download_archive_chunked');
		$this->requires(['folder_upload_max_depth'], 'folder_upload_enabled');
		$this->requires(['embed_metadata_update_checksum_enabled'], 'embed_metadata_in_files_enabled');
		$this->requires([
			'zip_bomb_max_total_size',
			'zip_bomb_max_file_size',
			'zip_bomb_max_entries',
			'zip_bomb_max_ratio',
			'zip_bomb_delete_rejected_file',
		], 'extract_zip_on_upload');

		// Mod Back Button
		$this->requires(['back_button_text', 'back_button_url'], 'back_button_enabled');

		// Mod Cache
		$this->requires(['cache_ttl'], 'cache_enabled');
		$this->requires(['managed_cache_albums_enabled', 'managed_cache_ttl'], 'managed_cache_enabled');

		// Mod Flow (hide_nsfw_in_flow and flow_blur_nsfw_enabled live in Mod NSFW)
		$this->requires([
			'flow_public',
			'flow_base',
			'flow_max_items',
			'flow_strategy',
			'flow_include_sub_albums',
			'flow_include_photos_from_children',
			'flow_open_album_on_click',
			'flow_display_open_album_button',
			'flow_highlight_first_picture',
			'flow_min_max_enabled',
			'flow_display_statistics',
			'flow_compact_mode_enabled',
			'flow_image_header_enabled',
			'date_format_flow_published',
			'flow_blur_nsfw_enabled',
			'hide_nsfw_in_flow',
		], 'flow_enabled');
		$this->requires(['flow_min_max_order', 'date_format_flow_min_max'], 'flow_enabled,flow_min_max_enabled');
		$this->requires([
			'flow_image_header_cover',
			'flow_image_header_height',
			'flow_carousel_enabled',
		], 'flow_enabled,flow_image_header_enabled');
		$this->requires(['flow_carousel_height'], 'flow_enabled,flow_image_header_enabled,flow_carousel_enabled');

		// Mod Frame (hide_nsfw_in_frame lives in Mod NSFW)
		$this->requires(['random_album_id', 'mod_frame_refresh', 'hide_nsfw_in_frame'], 'mod_frame_enabled');

		// Mod Map (hide_nsfw_in_map lives in Mod NSFW)
		$this->requires([
			'map_provider',
			'map_include_subalbums',
			'map_display_direction',
			'map_display_public',
			'hide_nsfw_in_map',
		], 'map_display');
		$this->requires(['location_decoding_timeout', 'location_show'], 'location_decoding');
		$this->requires(['location_show_public'], 'location_decoding,location_show');
		$this->requires(['gps_coordinate_display_public'], 'gps_coordinate_display');

		// Mod NSFW (module-specific filters)
		$this->requires(['hide_nsfw_in_landing_page'], 'landing_page_enable');
		$this->requires(['hide_nsfw_in_rss'], 'rss_enable');
		$this->requires(['hide_nsfw_in_timeline'], 'timeline_page_enabled');

		// Mod Privacy
		$this->requires([
			'temporary_image_link_when_logged_in',
			'temporary_image_link_when_admin',
			'temporary_image_link_life_in_seconds',
		], 'temporary_image_link_enabled');

		// Mod Pro
		$this->requires(['metrics_access'], 'metrics_enabled');
		$this->requires([
			'live_metrics_access',
			'live_metrics_max_time',
			'live_metrics_result_limit',
			'live_metrics_cleanup',
		], 'live_metrics_enabled');
		$this->requires(['album_header_landing_title_enabled'], 'album_enhanced_display_enabled');

		// Mod RSS
		$this->requires(['rss_title', 'rss_description', 'rss_max_items', 'rss_recent_days'], 'rss_enable');

		// Mod Rating
		$this->requires([
			'rating_public',
			'rating_show_only_when_user_rated',
			'rating_show_avg_in_details',
			'rating_photo_view_mode',
			'rating_show_avg_in_photo_view',
			'rating_album_view_mode',
			'rating_show_avg_in_album_view',
		], 'rating_enabled');

		// Mod Renamer
		$this->requires(['renamer_enforced', 'renamer_photo_title_enabled', 'renamer_album_title_enabled'], 'renamer_enabled');
		$this->requires(['renamer_enforced_before', 'renamer_enforced_after'], 'renamer_enabled,renamer_enforced');

		// Mod Timeline (timeline page only; per-album timelines have no single switch)
		$this->requires([
			'timeline_photos_layout',
			'timeline_photos_order',
			'timeline_photos_pagination_limit',
			'timeline_quick_access_date_format_year',
			'timeline_quick_access_date_format_month',
			'timeline_quick_access_date_format_day',
			'timeline_quick_access_date_format_hour',
		], 'timeline_page_enabled');

		// Mod Watermarker
		$this->requires([
			'watermark_photo_id',
			'watermark_random_path',
			'watermark_public',
			'watermark_logged_in_users_enabled',
			'watermark_original',
			'watermark_size',
			'watermark_opacity',
			'watermark_position',
			'watermark_shift_type',
			'watermark_shift_x',
			'watermark_shift_x_direction',
			'watermark_shift_y',
			'watermark_shift_y_direction',
			'watermark_optout_disabled',
		], 'watermark_enabled');

		// Mod Webshop
		$this->requires([
			'webshop_currency',
			'webshop_default_description',
			'webshop_allow_guest_checkout',
			'webshop_terms_url',
			'webshop_privacy_url',
			'webshop_default_price_cents',
			'webshop_default_license',
			'webshop_default_size',
			'webshop_offline',
			'webshop_lycheeorg_disclaimer_enabled',
			'webshop_auto_fulfill_enabled',
			'webshop_manual_fulfill_enabled',
		], 'webshop_enabled');

		// Mod Welcome (landing_title and landing_background_landscape are also used outside the landing page)
		$this->requires([
			'landing_subtitle',
			'landing_background_landscape_mode',
			'landing_background_portrait',
			'landing_background_portrait_mode',
			'landing_logo',
			'landing_header_logo',
			'landing_layout',
			'landing_intro_screen_enabled',
			'landing_hero_text_position',
			'landing_hero_text_color',
			'landing_hero_text_opacity',
			'landing_animation_preset',
			'landing_about_enabled',
			'landing_featured_items_enabled',
			'landing_cta_text',
			'landing_cta_position',
			'landing_cta_shift_type',
			'landing_cta_shift_x',
			'landing_cta_shift_x_direction',
			'landing_cta_shift_y',
			'landing_cta_shift_y_direction',
			'landing_meridian_explore_offset',
			'landing_meridian_contact_offset',
			'landing_meridian_explore_line_position',
			'landing_meridian_contact_line_position',
			'landing_login_position',
			'landing_backdrop_opacity',
		], 'landing_page_enable');
		$this->requires(['landing_about_text'], 'landing_page_enable,landing_about_enabled');
		$this->requires(['landing_featured_items_mode', 'landing_featured_items_count'], 'landing_page_enable,landing_featured_items_enabled');
		$this->requires(['gallery_header_logged_in_enabled', 'gallery_header', 'gallery_header_bar_transparent'], 'gallery_header_enabled');
		$this->requires(['gallery_header_bar_gradient'], 'gallery_header_enabled,gallery_header_bar_transparent');

		// Smart Albums
		$this->requires(['recent_age'], 'enable_recent');
		$this->requires(['best_pictures_count'], 'enable_best_pictures');
		$this->requires(['my_best_pictures_count'], 'enable_my_best_pictures');

		// AI Vision (person albums and their NSFW filter live in Smart Albums / Mod NSFW)
		$this->requires(['ai_vision_face_enabled', 'ai_vision_nsfw_enabled'], 'ai_vision_enabled');
		$this->requires([
			'ai_vision_face_permission_mode',
			'ai_vision_face_selfie_confidence_threshold',
			'ai_vision_face_person_is_searchable_default',
			'ai_vision_face_allow_user_claim',
			'ai_vision_face_overlay_enabled',
			'ai_vision_face_recognition_warning',
			'PA_override_visibility',
			'PA_override_searchability',
			'PA_albums_listing_enabled',
			'hide_nsfw_in_person_albums',
		], 'ai_vision_enabled,ai_vision_face_enabled');
		$this->requires(['ai_vision_face_overlay_default_visibility'], 'ai_vision_enabled,ai_vision_face_enabled,ai_vision_face_overlay_enabled');
		$this->requires([
			'ai_vision_nsfw_preset',
			'ai_vision_nsfw_check_block_action',
			'ai_vision_nsfw_monitor_block_action',
			'ai_vision_nsfw_trust_but_verify_block_action',
			'ai_vision_nsfw_trust_block_action',
			'ai_vision_nsfw_sensitive_album_action',
			'ai_vision_nsfw_sensitive_no_album_action',
			'ai_vision_nsfw_scan_trusted_users',
			'ai_vision_nsfw_monitor_hide_on_scan',
			'ai_vision_nsfw_trust_but_verify_hide_on_scan',
			'ai_vision_nsfw_trust_hide_on_scan',
		], 'ai_vision_enabled,ai_vision_nsfw_enabled');

		// access_permissions
		$this->requires(['login_required_root_only'], 'login_required');

		// contact
		$this->requires([
			'contact_form_security_question',
			'contact_form_security_answer',
			'contact_form_custom_consent_required',
			'contact_form_header',
			'contact_form_headline',
			'contact_form_contact_method',
			'contact_form_message_label',
			'contact_form_message_answer',
			'contact_form_custom_submit_button_text',
			'contact_form_thank_you_message',
			'contact_form_enabled_on_gallery',
			'contact_form_enabled_on_album',
			'contact_form_enabled_for_logged_in',
		], 'contact_form_enabled');
		$this->requires(['contact_form_custom_consent_text', 'contact_form_custom_privacy_url'], 'contact_form_enabled,contact_form_custom_consent_required');

		// gestures
		$this->requires(['photo_minimap_idle_opacity'], 'is_photo_minimap_enabled');
		$this->requires(['photo_minimap_idle_opacity_mobile'], 'is_photo_minimap_enabled_mobile');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->dropColumn(self::COL);
		});
	}

	/**
	 * @param string[] $keys          configs whose effect depends on $required_keys
	 * @param string   $required_keys comma-separated boolean configs that must all be `1`
	 */
	private function requires(array $keys, string $required_keys): void
	{
		DB::table('configs')->whereIn('key', $keys)->update([self::COL => $required_keys]);
	}
};
