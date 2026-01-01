<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Tags;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Tag;
use App\Policies\TagPolicy;
use Illuminate\Support\Facades\Gate;

class ListTagRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(TagPolicy::CAN_LIST, [Tag::class]);
	}
}
