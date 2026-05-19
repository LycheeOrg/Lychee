<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\Face;

trait HasFaceTrait
{
	protected Face $face;

	public function face(): Face
	{
		return $this->face;
	}
}
