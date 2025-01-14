<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration to increase decoupling of the Web User Interface (WUI) from the
 * backend.
 *
 * Some configuration settings which defined the visual appearance of the
 * WUI have historically only been used by the backend when some parts of the
 * frontend have been rendered via Blade templates.
 * If the WUI does so on its own, these settings must be sent to the WUI
 * and hence need to be public.
 * Note, this is no security infringement as the result of these configuration
 * settings is visible on the WUI anyway.
 *
 * On top, this migration also restructures the configuration settings.
 */
return new class() extends Migration {
	public const CONF_CATEGORY_SOCIAL_MEDIA = 'Social Media';
	public const CONF_CATEGORY_FOOTER = 'Footer';
	public const CONF_CATEGORY_CONF = 'config';
	public const CONF_CATEGORY_LANDING = 'Mod Welcome';

	/**
	 * Run the migrations.
	 *
	 * @throws InvalidArgumentException
	 */
	public function up(): void
	{
		// The "owner" information is not only used on the landing page,
		// but in the general footer of the gallery as well.
		// Rename that to reflect this.

		DB::table('configs')
			->where('key', '=', 'landing_owner')
			->update([
				'key' => 'site_owner',
				'cat' => self::CONF_CATEGORY_CONF,
				'confidentiality' => 0,
			]);

		// The social media links are not only used on the landing page,
		// but also in the general footer of the gallery as well, if enabled.
		// Rename them to reflect this and put them into their own category
		// to keep them together.

		DB::table('configs')
			->where('key', '=', 'landing_facebook')
			->update([
				'key' => 'sm_facebook_url',
				'cat' => self::CONF_CATEGORY_SOCIAL_MEDIA,
				'confidentiality' => 0,
			]);
		DB::table('configs')
			->where('key', '=', 'landing_flickr')
			->update([
				'key' => 'sm_flickr_url',
				'cat' => self::CONF_CATEGORY_SOCIAL_MEDIA,
				'confidentiality' => 0,
			]);
		DB::table('configs')
			->where('key', '=', 'landing_twitter')
			->update([
				'key' => 'sm_twitter_url',
				'cat' => self::CONF_CATEGORY_SOCIAL_MEDIA,
				'confidentiality' => 0,
			]);
		DB::table('configs')
			->where('key', '=', 'landing_instagram')
			->update([
				'key' => 'sm_instagram_url',
				'cat' => self::CONF_CATEGORY_SOCIAL_MEDIA,
				'confidentiality' => 0,
			]);
		DB::table('configs')
			->where('key', '=', 'landing_youtube')
			->update([
				'key' => 'sm_youtube_url',
				'cat' => self::CONF_CATEGORY_SOCIAL_MEDIA,
				'confidentiality' => 0,
			]);

		// Make footer settings public and group them together

		DB::table('configs')
			->where('key', '=', 'display_social_in_gallery')
			->update([
				'key' => 'footer_show_social_media',
				'cat' => self::CONF_CATEGORY_FOOTER,
				'confidentiality' => 0,
			]);
		DB::table('configs')
			->where('key', '=', 'site_copyright_enable')
			->update([
				'key' => 'footer_show_copyright',
				'cat' => self::CONF_CATEGORY_FOOTER,
				'confidentiality' => 0,
			]);
		DB::table('configs')
			->where('key', '=', 'additional_footer_text')
			->update([
				'key' => 'footer_additional_text',
				'cat' => self::CONF_CATEGORY_FOOTER,
				'confidentiality' => 0,
			]);

		// Make copyright settings available to WUI

		DB::table('configs')
			->where('key', '=', 'site_copyright_begin')
			->update(['confidentiality' => 0]);
		DB::table('configs')
			->where('key', '=', 'site_copyright_end')
			->update(['confidentiality' => 0]);

		// Rename NSFW text config, make it available to WUI and clear its
		// value, if it still equals the default value in order to use the
		// localized variant in the default case
		DB::table('configs')
			->where('key', '=', 'nsfw_warning_text')
			->update([
				'key' => 'nsfw_banner_override',
				'type_range' => 'string',
				'confidentiality' => 0,
			]);
		DB::table('configs')
			->where('key', '=', 'nsfw_banner_override')
			->where('value', '=', '<h1>Sensitive content</h1><p>This album contains sensitive content which some people may find offensive or disturbing.</p><p>Tap to consent.</p>')
			->update(['value' => '']);

		// Make setting key use small letters like everywhere else
		DB::table('configs')
			->where('key', '=', 'Mod_Frame')
			->update(['key' => 'mod_frame_enabled']);
		DB::table('configs')
			->where('key', '=', 'Mod_Frame_refresh')
			->update(['key' => 'mod_frame_refresh']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @throws InvalidArgumentException
	 */
	public function down(): void
	{
		DB::table('configs')
			->where('key', '=', 'site_owner')
			->update([
				'key' => 'landing_owner',
				'cat' => self::CONF_CATEGORY_LANDING,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'sm_facebook_url')
			->update([
				'key' => 'landing_facebook',
				'cat' => self::CONF_CATEGORY_LANDING,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'sm_flickr_url')
			->update([
				'key' => 'landing_flickr',
				'cat' => self::CONF_CATEGORY_LANDING,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'sm_twitter_url')
			->update([
				'key' => 'landing_twitter',
				'cat' => self::CONF_CATEGORY_LANDING,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'sm_instagram_url')
			->update([
				'key' => 'landing_instagram',
				'cat' => self::CONF_CATEGORY_LANDING,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'sm_youtube_url')
			->update([
				'key' => 'landing_youtube',
				'cat' => self::CONF_CATEGORY_LANDING,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'footer_show_social_media')
			->update([
				'key' => 'display_social_in_gallery',
				'cat' => self::CONF_CATEGORY_CONF,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'footer_show_copyright')
			->update([
				'key' => 'site_copyright_enable',
				'cat' => self::CONF_CATEGORY_CONF,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'footer_additional_text')
			->update([
				'key' => 'additional_footer_text',
				'cat' => self::CONF_CATEGORY_CONF,
				'confidentiality' => 2,
			]);
		DB::table('configs')
			->where('key', '=', 'nsfw_banner_override')
			->update([
				'key' => 'nsfw_warning_text',
				'confidentiality' => 3,
				'type_range' => 'string_required',
			]);
		DB::table('configs')
			->where('key', '=', 'nsfw_warning_text')
			->where('value', '=', '')
			->update(['value' => '<h1>Sensitive content</h1><p>This album contains sensitive content which some people may find offensive or disturbing.</p><p>Tap to consent.</p>']);
		DB::table('configs')
			->where('key', '=', 'mod_frame_enabled')
			->update(['key' => 'Mod_Frame']);
		DB::table('configs')
			->where('key', '=', 'mod_frame_refresh')
			->update(['key' => 'Mod_Frame_refresh']);
	}
};
