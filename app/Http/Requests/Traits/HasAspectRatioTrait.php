<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\AspectRatioType;

trait HasAspectRatioTrait
{
	protected ?AspectRatioType $aspectRatio = null;

	/**
	 * @return AspectRatioType|null
	 */
	public function aspectRatio(): ?AspectRatioType
	{
		return $this->aspectRatio;
	}
}
