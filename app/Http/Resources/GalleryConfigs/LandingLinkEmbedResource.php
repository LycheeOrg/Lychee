<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\LandingLinkPlacement;
use App\Models\LandingLink;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Public-safe projection of a LandingLink for embedding into LandingPageResource.
 */
#[TypeScript()]
class LandingLinkEmbedResource extends Data
{
	public string $id;
	public string $label;
	/**
	 * A real URL for admin-created links. For built-in links (see
	 * `is_built_in`), this is a Vue Router route name instead, and the
	 * frontend must resolve it client-side rather than using it as an `href`.
	 */
	public string $url;
	public LandingLinkPlacement $placement;
	public bool $open_in_new_tab;
	public bool $is_built_in;

	public function __construct(LandingLink $landing_link)
	{
		$this->id = $landing_link->id;
		$this->label = $landing_link->label;
		$this->url = $landing_link->url;
		$this->placement = $landing_link->placement;
		$this->open_in_new_tab = $landing_link->open_in_new_tab;
		$this->is_built_in = $landing_link->is_built_in;
	}
}
