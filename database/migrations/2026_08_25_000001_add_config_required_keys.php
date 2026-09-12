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
	public const COL = 'required_keys';

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('configs', function (Blueprint $table) {
			$table->string(self::COL)->nullable()->after('is_expert');
		});

		// Landing page (cat: Mod Welcome), root: landing_page_enable
		DB::table('configs')->whereIn('key', [
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
		])->update([self::COL => 'landing_page_enable']);
		DB::table('configs')->where('key', 'landing_about_text')->update([self::COL => 'landing_page_enable,landing_about_enabled']);
		DB::table('configs')->whereIn('key', [
			'landing_featured_items_mode',
			'landing_featured_items_count',
		])->update([self::COL => 'landing_page_enable,landing_featured_items_enabled']);

		// Timeline (cat: Mod Timeline), two independent roots
		DB::table('configs')->whereIn('key', [
			'timeline_photos_public',
			'timeline_photos_granularity',
			'timeline_photos_order',
			'timeline_photos_layout',
			'timeline_photos_pagination_limit',
			'timeline_photo_date_format_year',
			'timeline_photo_date_format_month',
			'timeline_photo_date_format_day',
			'timeline_photo_date_format_hour',
		])->update([self::COL => 'timeline_photos_enabled']);
		DB::table('configs')->whereIn('key', [
			'timeline_albums_public',
			'timeline_albums_granularity',
			'timeline_albums_root_enabled',
			'timeline_album_date_format_year',
			'timeline_album_date_format_month',
			'timeline_album_date_format_day',
		])->update([self::COL => 'timeline_albums_enabled']);

		// AI Vision (cat: AI Vision), root: ai_vision_enabled
		DB::table('configs')->whereIn('key', [
			'ai_vision_face_enabled',
			'ai_vision_nsfw_enabled',
		])->update([self::COL => 'ai_vision_enabled']);
		DB::table('configs')->whereIn('key', [
			'ai_vision_face_permission_mode',
			'ai_vision_face_selfie_confidence_threshold',
			'ai_vision_face_person_is_searchable_default',
			'ai_vision_face_allow_user_claim',
			'ai_vision_face_overlay_enabled',
			'ai_vision_face_recognition_warning',
		])->update([self::COL => 'ai_vision_enabled,ai_vision_face_enabled']);
		DB::table('configs')->where('key', 'ai_vision_face_overlay_default_visibility')
			->update([self::COL => 'ai_vision_enabled,ai_vision_face_enabled,ai_vision_face_overlay_enabled']);
		DB::table('configs')->whereIn('key', [
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
		])->update([self::COL => 'ai_vision_enabled,ai_vision_nsfw_enabled']);

		// Watermarker (cat: Mod Watermarker), root: watermark_enabled
		DB::table('configs')->whereIn('key', [
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
		])->update([self::COL => 'watermark_enabled']);

		// Frame (cat: Mod Frame), root: mod_frame_enabled
		DB::table('configs')->whereIn('key', [
			'random_album_id',
			'mod_frame_refresh',
		])->update([self::COL => 'mod_frame_enabled']);

		// Map (cat: Mod Map), root: map_display
		DB::table('configs')->whereIn('key', [
			'map_provider',
			'map_include_subalbums',
			'map_display_direction',
			'map_display_public',
			'gps_coordinate_display',
			'gps_coordinate_display_public',
		])->update([self::COL => 'map_display']);

		// Flow (cat: Mod Flow), root: flow_enabled
		DB::table('configs')->whereIn('key', [
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
			'flow_carousel_enabled',
			'date_format_flow_published',
			'flow_blur_nsfw_enabled',
			'hide_nsfw_in_flow',
		])->update([self::COL => 'flow_enabled']);
		DB::table('configs')->whereIn('key', [
			'flow_min_max_order',
			'date_format_flow_min_max',
		])->update([self::COL => 'flow_enabled,flow_min_max_enabled']);
		DB::table('configs')->whereIn('key', [
			'flow_image_header_cover',
			'flow_image_header_height',
		])->update([self::COL => 'flow_enabled,flow_image_header_enabled']);
		DB::table('configs')->where('key', 'flow_carousel_height')->update([self::COL => 'flow_enabled,flow_carousel_enabled']);
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
};
