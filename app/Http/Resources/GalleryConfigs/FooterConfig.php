<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class FooterConfig extends Data
{
	public string $footer_additional_text;
	public bool $footer_show_copyright;
	public bool $footer_show_social_media;
	public string $copyright;
	public string $sm_facebook_url;
	public string $sm_flickr_url;
	public string $sm_instagram_url;
	public string $sm_twitter_url;
	public string $sm_youtube_url;

	public function __construct()
	{
		$this->footer_additional_text = Configs::getValueAsString('footer_additional_text');
		$this->footer_show_copyright = Configs::getValueAsBool('footer_show_copyright');
		$this->footer_show_social_media = Configs::getValueAsBool('footer_show_social_media');
		$this->copyright = $this->get_copyright();
		$this->sm_facebook_url = Configs::getValueAsString('sm_facebook_url');
		$this->sm_flickr_url = Configs::getValueAsString('sm_flickr_url');
		$this->sm_instagram_url = Configs::getValueAsString('sm_instagram_url');
		$this->sm_twitter_url = Configs::getValueAsString('sm_twitter_url');
		$this->sm_youtube_url = Configs::getValueAsString('sm_youtube_url');
	}

	private function get_copyright(): string
	{
		$copyright_text = Configs::getValueAsString('copyright_text');
		if ($copyright_text !== '') {
			return $copyright_text;
		}

		$site_copyright_begin = Configs::getValueAsString('site_copyright_begin');
		$site_copyright_end = Configs::getValueAsString('site_copyright_end');
		$copyright_year = $site_copyright_begin;
		if ($site_copyright_begin !== $site_copyright_end) {
			$copyright_year = $copyright_year . '-' . $site_copyright_end;
		}

		$site_owner = Configs::getValueAsString('site_owner');
		return $copyright_year !== '' ? sprintf(__('landing.copyright'), $site_owner, $copyright_year) : '';
	}
}
