<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'version')->update(['description' => 'Current version of Lychee']);
		DB::table('configs')->where('key', 'check_for_updates')->update(['description' => 'Automatically check for new updates']);
		DB::table('configs')->where('key', 'sorting_photos_col')->update(['description' => 'Default column used for sorting photos']);
		DB::table('configs')->where('key', 'sorting_photos_order')->update(['description' => 'Default order used for sorting photos']);
		DB::table('configs')->where('key', 'sorting_albums_col')->update(['description' => 'Default column used for sorting albums']);
		DB::table('configs')->where('key', 'sorting_albums_order')->update(['description' => 'Default order used for sorting albums']);
		DB::table('configs')->where('key', 'imagick')->update(['description' => 'Enable imagick processing']);
		DB::table('configs')->where('key', 'dropbox_key')->update(['description' => 'Dropbox API key']);
		DB::table('configs')->where('key', 'skip_duplicates')->update(['description' => 'Skip duplicate if found on import']);
		DB::table('configs')->where('key', 'lang')->update(['description' => 'Language used by Lychee']);
		DB::table('configs')->where('key', 'layout')->update(['description' => 'Layout for pictures']);
		DB::table('configs')->where('key', 'default_license')->update(['description' => 'Default license used for albums']);
		DB::table('configs')->where('key', 'small_max_width')->update(['description' => 'Maximum width for small thumbs ((un)justified album view)']);
		DB::table('configs')->where('key', 'small_max_height')->update(['description' => 'Maximum height for small thumbs ((un)justified album view)']);
		DB::table('configs')->where('key', 'medium_max_width')->update(['description' => 'Maximum width for medium image (photo view)']);
		DB::table('configs')->where('key', 'medium_max_height')->update(['description' => 'Maximum height for medium image (photo view)']);
		DB::table('configs')->where('key', 'full_photo')->update(['description' => 'Allows access to full resolution by default']);
		DB::table('configs')->where('key', 'delete_imported')->update(['description' => 'When importing from server, delete originals']);
		DB::table('configs')->where('key', 'Mod_Frame')->update(['description' => 'Enable Frame mode']);
		DB::table('configs')->where('key', 'Mod_Frame_refresh')->update(['description' => 'Refresh rate of the Frame']);
		DB::table('configs')->where('key', 'image_overlay_type')->update(['description' => 'Default image overlay information']);
		DB::table('configs')->where('key', 'compression_quality')->update(['description' => 'Compression percent when generating thumbs']);
		DB::table('configs')->where('key', 'landing_page_enable')->update(['description' => 'Display the landing page']);
		DB::table('configs')->where('key', 'landing_owner')->update(['description' => 'Owner of the Website']);
		DB::table('configs')->where('key', 'landing_title')->update(['description' => 'Title on the landing page']);
		DB::table('configs')->where('key', 'landing_subtitle')->update(['description' => 'Subtitle on the landing page']);
		DB::table('configs')->where('key', 'landing_facebook')->update(['description' => 'Link to facebook user account']);
		DB::table('configs')->where('key', 'landing_flickr')->update(['description' => 'Link to flickr user account']);
		DB::table('configs')->where('key', 'landing_twitter')->update(['description' => 'Link to twitter user account']);
		DB::table('configs')->where('key', 'landing_instagram')->update(['description' => 'Link to instagram user account']);
		DB::table('configs')->where('key', 'landing_youtube')->update(['description' => 'Link to youtube user account']);
		DB::table('configs')->where('key', 'landing_background')->update(['description' => 'URL of background image']);
		DB::table('configs')->where('key', 'thumb_2x')->update(['description' => 'Enable 2x size of square thumbs']);
		DB::table('configs')->where('key', 'small_2x')->update(['description' => 'Enable 2x size of small thumbs']);
		DB::table('configs')->where('key', 'medium_2x')->update(['description' => 'Enable 2x size of medium pictures']);
		DB::table('configs')->where('key', 'site_title')->update(['description' => 'Website title']);
		DB::table('configs')->where('key', 'site_copyright_enable')->update(['description' => 'Enable copyright notice at the bottom']);
		DB::table('configs')->where('key', 'site_copyright_begin')->update(['description' => 'Initial year of copyright']);
		DB::table('configs')->where('key', 'site_copyright_end')->update(['description' => 'Last year of copyright']);
		DB::table('configs')->where('key', 'api_key')->update(['description' => 'Deprecated']);
		DB::table('configs')->where('key', 'allow_online_git_pull')->update(['description' => 'Allow git pull via web interface']);
		DB::table('configs')->where('key', 'force_migration_in_production')->update(['description' => 'Force migration even if app is in production mode']);
		DB::table('configs')->where('key', 'additional_footer_text')->update(['description' => 'Extra text at the bottom of the page']);
		DB::table('configs')->where('key', 'display_social_in_gallery')->update(['description' => 'Display social links at the bottom of the gallery']);
		DB::table('configs')->where('key', 'public_search')->update(['description' => 'Allows anonymous user to use the Search bar']);
		DB::table('configs')->where('key', 'gen_demo_js')->update(['description' => 'Enable generation of JS responses for demo purposes']);
		DB::table('configs')->where('key', 'hide_version_number')->update(['description' => 'Hide current version number']);
		DB::table('configs')->where('key', 'public_recent')->update(['description' => 'Make Recent smart album accessible to anonymous users']);
		DB::table('configs')->where('key', 'recent_age')->update(['description' => 'Maximum age of pictures in Recent in days']);
		DB::table('configs')->where('key', 'public_starred')->update(['description' => 'Make Starred smart album accessible to anonymous users']);
		DB::table('configs')->where('key', 'SL_enable')->update(['description' => 'Enable symbolic link protection']);
		DB::table('configs')->where('key', 'SL_for_admin')->update(['description' => 'Enable symbolic links on logged in admin user']);
		DB::table('configs')->where('key', 'SL_life_time_days')->update(['description' => 'Maximum life time for symbolic link']);
		DB::table('configs')->where('key', 'photos_wraparound')->update(['description' => 'Once reaching last picture of an album, loop back to the start']);
		DB::table('configs')->where('key', 'raw_formats')->update(['description' => 'Allowed extra formats, will not be processed']);
		DB::table('configs')->where('key', 'map_display')->update(['description' => 'Display the map given GPS coordinates']);
		DB::table('configs')->where('key', 'zip64')->update(['description' => 'Use Zip 64bits instead of 32 bits']);
		DB::table('configs')->where('key', 'force_32bit_ids')->update(['description' => 'Force 32 bit legacy identifiers in the database']);
		DB::table('configs')->where('key', 'map_display_public')->update(['description' => 'Allow anonymous users to access the map']);
		DB::table('configs')->where('key', 'map_provider')->update(['description' => 'Defines the map provider']);
		DB::table('configs')->where('key', 'map_include_subalbums')->update(['description' => 'Includes pictures of the sub albums on the map']);
		DB::table('configs')->where('key', 'update_check_every_days')->update(['description' => 'Frequency of Lychee update checks']);
		DB::table('configs')->where('key', 'has_exiftool')->update(['description' => 'Defines whether exiftool processing is available']);
		DB::table('configs')->where('key', 'share_button_visible')->update(['description' => 'Share button visibility in the header']);
		DB::table('configs')->where('key', 'has_ffmpeg')->update(['description' => 'Defines whether ffmpeg processing is available']);
		DB::table('configs')->where('key', 'import_via_symlink')->update(['description' => 'Use symbolic links instead of copying the original on import from server']);
		DB::table('configs')->where('key', 'apply_composer_update')->update(['description' => 'Apply composer update on lychee update via web interface']);
		DB::table('configs')->where('key', 'location_decoding')->update(['description' => 'Use GPS location decoding']);
		DB::table('configs')->where('key', 'location_decoding_timeout')->update(['description' => 'Timeout for the GPS decoding queries']);
		DB::table('configs')->where('key', 'location_show')->update(['description' => 'Show location extracted from GPS coordinates']);
		DB::table('configs')->where('key', 'location_show_public')->update(['description' => 'Anonymous users can acess the extracted location from GPS coordinates']);
		DB::table('configs')->where('key', 'rss_enable')->update(['description' => 'Enable RSS feed']);
		DB::table('configs')->where('key', 'rss_recent_days')->update(['description' => 'Display the last X days in the RSS feed']);
		DB::table('configs')->where('key', 'rss_max_items')->update(['description' => 'Max number of items in the RSS feed']);
		DB::table('configs')->where('key', 'prefer_available_xmp_metadata')->update(['description' => 'Use sidecar if provided instead of exif metadata']);
		DB::table('configs')->where('key', 'editor_enabled')->update(['description' => 'Enable manual rotation of images']);
		DB::table('configs')->where('key', 'lossless_optimization')->update(['description' => 'Apply additional compression on images']);
		DB::table('configs')->where('key', 'swipe_tolerance_x')->update(['description' => 'Defines default horizontal swipe tolerance for mobile interactions']);
		DB::table('configs')->where('key', 'swipe_tolerance_y')->update(['description' => 'Defines default vertical swipe tolerance for mobile interactions']);
		DB::table('configs')->where('key', 'log_max_num_line')->update(['description' => 'Display the last X most recent lines in Logs']);
		DB::table('configs')->where('key', 'unlock_password_photos_with_url_param')->update(['description' => 'Allow password to be passed as a URL parameter to unlock albums']);
		DB::table('configs')->where('key', 'nsfw_visible')->update(['description' => 'Make sensitive albums visible by default']);
		DB::table('configs')->where('key', 'nsfw_blur')->update(['description' => 'Blur sensitive albums']);
		DB::table('configs')->where('key', 'nsfw_warning')->update(['description' => 'Enable sensitive albums warning']);
		DB::table('configs')->where('key', 'nsfw_warning_admin')->update(['description' => 'Enable sensitive albums warning when logged in']);
		DB::table('configs')->where('key', 'nsfw_warning_text')->update(['description' => 'Text of the sensitive albums warning']);
		DB::table('configs')->where('key', 'map_display_direction')->update(['description' => 'Display the direction of the picture on the map if available']);
		DB::table('configs')->where('key', 'album_subtitle_type')->update(['description' => 'Defines the subtitle of album in albums view']);
		DB::table('configs')->where('key', 'upload_processing_limit')->update(['description' => 'Maximum number of images processed in parallel']);
		DB::table('configs')->where('key', 'public_photos_hidden')->update(['description' => 'Keep singular public pictures hidden from search results, smart albums & tag albums']);
		DB::table('configs')->where('key', 'new_photos_notification')->update(['description' => 'Enable notifications when new photos are added']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->whereIn('key', ['version', 'check_for_updates', 'sorting_photos_col', 'sorting_photos_order', 'sorting_albums_col', 'sorting_albums_order', 'imagick', 'dropbox_key', 'skip_duplicates', 'lang', 'layout', 'default_license', 'small_max_width', 'small_max_height', 'medium_max_width', 'medium_max_height', 'full_photo', 'delete_imported', 'Mod_Frame', 'Mod_Frame_refresh', 'image_overlay_type', 'compression_quality', 'landing_page_enable', 'landing_owner', 'landing_title', 'landing_subtitle', 'landing_facebook', 'landing_flickr', 'landing_twitter', 'landing_instagram', 'landing_youtube', 'landing_background', 'thumb_2x', 'small_2x', 'medium_2x', 'site_title', 'site_copyright_enable', 'site_copyright_begin', 'site_copyright_end', 'api_key', 'allow_online_git_pull', 'force_migration_in_production', 'additional_footer_text', 'display_social_in_gallery', 'public_search', 'gen_demo_js', 'hide_version_number', 'public_recent', 'recent_age', 'public_starred', 'SL_enable', 'SL_for_admin', 'SL_life_time_days', 'photos_wraparound', 'raw_formats', 'map_display', 'zip64', 'force_32bit_ids', 'map_display_public', 'map_provider', 'map_include_subalbums', 'update_check_every_days', 'has_exiftool', 'share_button_visible', 'has_ffmpeg', 'import_via_symlink', 'apply_composer_update', 'location_decoding', 'location_decoding_timeout', 'location_show', 'location_show_public', 'rss_enable', 'rss_recent_days', 'rss_max_items', 'prefer_available_xmp_metadata', 'editor_enabled', 'lossless_optimization', 'swipe_tolerance_x', 'swipe_tolerance_y', 'log_max_num_line', 'unlock_password_photos_with_url_param', 'nsfw_visible', 'nsfw_blur', 'nsfw_warning', 'nsfw_warning_admin', 'nsfw_warning_text', 'map_display_direction', 'album_subtitle_type', 'upload_processing_limit', 'public_photos_hidden', 'new_photos_notification'])->update(['description' => '']);
	}
};
