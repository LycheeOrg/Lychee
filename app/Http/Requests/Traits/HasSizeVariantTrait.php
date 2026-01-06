<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\DownloadVariantType;

trait HasSizeVariantTrait
{
	protected ?DownloadVariantType $size_variant;

	/**
	 * @return ?DownloadVariantType
	 */
	public function sizeVariant(): ?DownloadVariantType
	{
		return $this->size_variant;
	}
}
