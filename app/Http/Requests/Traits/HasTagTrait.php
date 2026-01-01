<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\Tag;

trait HasTagTrait
{
	protected Tag $tag;

	/**
	 * @return Tag
	 */
	public function tag(): Tag
	{
		return $this->tag;
	}
}
