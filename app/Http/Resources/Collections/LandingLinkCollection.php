<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\LandingLinkResource;
use App\Models\LandingLink;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class LandingLinkCollection extends Data
{
	/** @var Collection<int,LandingLinkResource> */
	#[LiteralTypeScriptType('App.Http.Resources.Models.LandingLinkResource[]')]
	public Collection $landing_links;

	/**
	 * @param Collection<int,LandingLink> $landing_links
	 */
	public function __construct(Collection $landing_links)
	{
		$this->landing_links = $landing_links->map(fn (LandingLink $landing_link) => new LandingLinkResource($landing_link));
	}
}
