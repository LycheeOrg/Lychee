<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\LandingLinkPlacement;
use App\Models\LandingLink;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LandingLinkResource extends Data
{
	public string $id;
	public string $label;
	public string $url;
	public LandingLinkPlacement $placement;
	public bool $open_in_new_tab;
	public int $sort_order;
	public bool $enabled;
	public bool $is_built_in;
	public Carbon $created_at;
	public Carbon $updated_at;

	public function __construct(LandingLink $landing_link)
	{
		$this->id = $landing_link->id;
		$this->label = $landing_link->label;
		$this->url = $landing_link->url;
		$this->placement = $landing_link->placement;
		$this->open_in_new_tab = $landing_link->open_in_new_tab;
		$this->sort_order = $landing_link->sort_order;
		$this->enabled = $landing_link->enabled;
		$this->is_built_in = $landing_link->is_built_in;
		$this->created_at = $landing_link->created_at;
		$this->updated_at = $landing_link->updated_at;
	}
}
