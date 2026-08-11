<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\LandingFeaturedItemType;
use App\Models\LandingFeaturedItem;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Admin CRUD projection of a LandingFeaturedItem (raw DB row shape).
 */
#[TypeScript()]
class LandingFeaturedItemResource extends Data
{
	public string $id;
	public LandingFeaturedItemType $item_type;
	public string $item_id;
	public int $sort_order;
	public bool $enabled;
	public Carbon $created_at;
	public Carbon $updated_at;

	public function __construct(LandingFeaturedItem $landing_featured_item)
	{
		$this->id = $landing_featured_item->id;
		$this->item_type = $landing_featured_item->item_type;
		$this->item_id = $landing_featured_item->item_id;
		$this->sort_order = $landing_featured_item->sort_order;
		$this->enabled = $landing_featured_item->enabled;
		$this->created_at = $landing_featured_item->created_at;
		$this->updated_at = $landing_featured_item->updated_at;
	}
}
