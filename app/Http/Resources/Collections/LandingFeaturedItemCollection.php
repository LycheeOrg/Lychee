<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\LandingFeaturedItemResource;
use App\Models\LandingFeaturedItem;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LandingFeaturedItemCollection extends Data
{
	/** @var Collection<int,LandingFeaturedItemResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.LandingFeaturedItemResource[]')]
	public Collection $landing_featured_items;

	/**
	 * @param Collection<int,LandingFeaturedItem> $landing_featured_items
	 */
	public function __construct(Collection $landing_featured_items)
	{
		$this->landing_featured_items = $landing_featured_items->map(fn (LandingFeaturedItem $item) => new LandingFeaturedItemResource($item));
	}
}
