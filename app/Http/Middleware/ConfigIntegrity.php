<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Small update of the database to avoid sneaky people setting all the levels to 0 and thus skipping some checks...
 */
class ConfigIntegrity
{
	public const SE_FIELDS = [
		'default_user_quota',
		'disable_small_download',
		'disable_small2x_download',
		'disable_medium_download',
		'disable_medium2x_download',
		'timeline_photos_granularity',
		'timeline_albums_granularity',
		'timeline_left_border_enabled',
		'timeline_photo_date_format_year',
		'timeline_photo_date_format_month',
		'timeline_photo_date_format_day',
		'timeline_photo_date_format_hour',
		'timeline_album_date_format_year',
		'timeline_album_date_format_month',
		'timeline_album_date_format_day',
		'timeline_quick_access_date_format_year',
		'timeline_quick_access_date_format_month',
		'timeline_quick_access_date_format_day',
		'timeline_quick_access_date_format_hour',
		'number_albums_per_row_mobile',
		'client_side_favourite_enabled',
		'extract_zip_on_upload',
		'photo_thumb_tags_enabled',
		'cache_ttl',
		'secure_image_link_enabled',
		'exif_disabled_for_all',
		'file_name_hidden',
		'low_number_of_shoots_per_day',
		'medium_number_of_shoots_per_day',
		'high_number_of_shoots_per_day',
		'metrics_enabled',
		'metrics_logged_in_users_enabed',
		'metrics_access',
		'watermark_enabled',
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
		'live_metrics_enabled',
		'live_metrics_access',
		'live_metrics_max_time',
		'enable_colour_extractions',
		'colour_extraction_driver',
		'renamer_enabled',
		'renamer_enforced',
		'renamer_enforced_before',
		'renamer_enforced_after',
		'renamer_photo_title_enabled',
		'renamer_album_title_enabled',
		'flow_strategy',
		'flow_compact_mode_enabled',
		'flow_include_sub_albums',
		'flow_include_photos_from_children',
		'flow_open_album_on_click',
		'flow_display_open_album_button',
		'flow_highlight_first_picture',
		'flow_min_max_enabled',
		'flow_display_statistics',
		'flow_image_header_enabled',
		'flow_image_header_cover',
		'flow_image_header_height',
		'flow_carousel_enabled',
		'flow_carousel_height',
		'date_format_flow_published',
		'date_format_flow_min_max',
		'rating_public',
		'rating_show_only_when_user_rated',
		'rating_photo_view_mode',
		'rating_show_avg_in_photo_view',
		'rating_album_view_mode',
		'enable_best_pictures',
		'best_pictures_count',
		'enable_my_best_pictures',
		'my_best_pictures_count',
	];

	public const PRO_FIELDS = [
		'webshop_enabled',
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
		'photos_star_visibility',
		'album_enhanced_display_enabled',
		'album_header_size',
		'album_header_landing_title_enabled',
	];

	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request                                                                          $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 *
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, \Closure $next)
	{
		try {
			DB::table('configs')->whereIn('key', self::SE_FIELDS)->update(['level' => 1]);
			DB::table('configs')->whereIn('key', self::PRO_FIELDS)->update(['level' => 2]);
		} catch (\Exception $e) {
			// Do nothing: we are not installed yet, so we fail silently.
		}

		return $next($request);
	}
}