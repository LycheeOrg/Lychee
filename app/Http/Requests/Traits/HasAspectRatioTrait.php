<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\AspectRatioType;

trait HasAspectRatioTrait
{
	protected ?AspectRatioType $aspect_ratio = null;

	/**
	 * @return AspectRatioType|null
	 */
	public function aspectRatio(): ?AspectRatioType
	{
		return $this->aspect_ratio;
	}
}
