<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Tags;

use App\Models\Tag;
use App\Policies\TagPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class TagsResource extends Data
{
	public bool $can_edit;

	#[LiteralTypeScriptType('App.Http.Resources.Tags.TagResource[]')]
	public Collection $tags;

	/**
	 * @param Collection<int,TagResource> $tags
	 *
	 * @return void
	 */
	public function __construct(
		$tags,
	) {
		$this->tags = $tags;
		$this->can_edit = Gate::check(TagPolicy::CAN_EDIT, [Tag::class]);
	}
}
