<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LandingPageResource extends Data
{
	public bool $landing_page_enable;
	public string $landing_background_landscape;
	public string $landing_background_portrait;
	public string $landing_subtitle;
	public string $landing_title;
	public string $site_owner;
	public string $site_title;
	public FooterConfig $footer;

	public function __construct()
	{
		$this->footer = new FooterConfig();
		$this->landing_page_enable = Configs::getValueAsBool('landing_page_enable');
		$this->landing_background_landscape = Configs::getValueAsString('landing_background_landscape');
		$this->landing_background_portrait = Configs::getValueAsString('landing_background_portrait');
		$this->landing_subtitle = Configs::getValueAsString('landing_subtitle');
		$this->landing_title = Configs::getValueAsString('landing_title');
		$this->site_owner = Configs::getValueAsString('site_owner');
		$this->site_title = Configs::getValueAsString('site_title');
	}
}
