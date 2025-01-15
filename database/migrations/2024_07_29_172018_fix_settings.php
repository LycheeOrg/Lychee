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
		DB::table('configs')->where('key', '=', 'site_owner')->update(['description' => 'Website Owner']);
		DB::table('configs')->where('key', '=', 'mod_frame_enabled')->update(['description' => 'Enable Mod Frame']);
		DB::table('configs')->where('key', '=', 'sm_facebook_url')->update(['description' => 'Url of facebook profile']);
		DB::table('configs')->where('key', '=', 'sm_flickr_url')->update(['description' => 'Url of flickr profile']);
		DB::table('configs')->where('key', '=', 'sm_twitter_url')->update(['description' => 'Url of twitter profile']);
		DB::table('configs')->where('key', '=', 'sm_instagram_url')->update(['description' => 'Url of instagram profile']);
		DB::table('configs')->where('key', '=', 'sm_youtube_url')->update(['description' => 'Url of youtube profile']);
		DB::table('configs')->where('key', '=', 'footer_show_copyright')->update(['description' => 'Display copyright in footer.']);
		DB::table('configs')->where('key', '=', 'footer_additional_text')->update(['description' => 'Additional text of the footer.']);
		DB::table('configs')->where('key', '=', 'footer_show_social_media')->update(['description' => 'Show social media links in footer.']);
		DB::table('configs')->where('key', '=', 'grants_download')->update(['description' => 'Grants download by default.']);
		DB::table('configs')->where('key', '=', 'nsfw_banner_override')->update(['description' => 'Custom warning text instead of default.']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// nothing to do.
	}
};
