<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LandingPageResource extends Data
{
	public string $footer_additional_text;
	public bool $footer_show_copyright;
	public bool $footer_show_social_media;
	public bool $landing_page_enable;
	public string $landing_background;
	public string $landing_subtitle;
	public string $landing_title;
	public int $site_copyright_begin;
	public int $site_copyright_end;
	public string $site_owner;
	public string $site_title;
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
		$this->landing_page_enable = Configs::getValueAsBool('landing_page_enable');
		$this->landing_background = Configs::getValueAsString('landing_background');
		$this->landing_subtitle = Configs::getValueAsString('landing_subtitle');
		$this->landing_title = Configs::getValueAsString('landing_title');
		$this->site_copyright_begin = Configs::getValueAsInt('site_copyright_begin');
		$this->site_copyright_end = Configs::getValueAsInt('site_copyright_end');
		$this->site_owner = Configs::getValueAsString('site_owner');
		$this->site_title = Configs::getValueAsString('site_title');
		$this->sm_facebook_url = Configs::getValueAsString('sm_facebook_url');
		$this->sm_flickr_url = Configs::getValueAsString('sm_flickr_url');
		$this->sm_instagram_url = Configs::getValueAsString('sm_instagram_url');
		$this->sm_twitter_url = Configs::getValueAsString('sm_twitter_url');
		$this->sm_youtube_url = Configs::getValueAsString('sm_youtube_url');
	}
}
