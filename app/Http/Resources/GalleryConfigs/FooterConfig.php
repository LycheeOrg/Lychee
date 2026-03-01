<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

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

	public bool $is_contact_form_enabled;
	public string $contact_header;

	public function __construct()
	{
		$this->footer_additional_text = request()->configs()->getValueAsString('footer_additional_text');
		$this->footer_show_copyright = request()->configs()->getValueAsBool('footer_show_copyright');
		$this->footer_show_social_media = request()->configs()->getValueAsBool('footer_show_social_media');
		$this->copyright = $this->get_copyright();
		$this->sm_facebook_url = request()->configs()->getValueAsString('sm_facebook_url');
		$this->sm_flickr_url = request()->configs()->getValueAsString('sm_flickr_url');
		$this->sm_instagram_url = request()->configs()->getValueAsString('sm_instagram_url');
		$this->sm_twitter_url = request()->configs()->getValueAsString('sm_twitter_url');
		$this->sm_youtube_url = request()->configs()->getValueAsString('sm_youtube_url');

		$this->is_contact_form_enabled = request()->configs()->getValueAsBool('contact_form_enabled');
		$this->contact_header = request()->configs()->getValueAsString('contact_form_header');
	}

	private function get_copyright(): string
	{
		$copyright_text = request()->configs()->getValueAsString('copyright_text');
		if ($copyright_text !== '') {
			return $copyright_text;
		}

		$site_copyright_begin = request()->configs()->getValueAsString('site_copyright_begin');
		$site_copyright_end = request()->configs()->getValueAsString('site_copyright_end');
		$copyright_year = $site_copyright_begin;
		if ($site_copyright_begin !== $site_copyright_end) {
			$copyright_year = $copyright_year . '-' . $site_copyright_end;
		}

		$site_owner = request()->configs()->getValueAsString('site_owner');

		return $copyright_year !== '' ? sprintf(__('landing.copyright'), $site_owner, $copyright_year) : '';
	}
}
