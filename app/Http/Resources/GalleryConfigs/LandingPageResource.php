<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

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
		$this->landing_page_enable = request()->configs()->getValueAsBool('landing_page_enable');
		$this->landing_background_landscape = request()->configs()->getValueAsString('landing_background_landscape');
		$this->landing_background_portrait = request()->configs()->getValueAsString('landing_background_portrait');
		$this->landing_subtitle = request()->configs()->getValueAsString('landing_subtitle');
		$this->landing_title = request()->configs()->getValueAsString('landing_title');
		$this->site_owner = request()->configs()->getValueAsString('site_owner');
		$this->site_title = request()->configs()->getValueAsString('site_title');
	}
}
