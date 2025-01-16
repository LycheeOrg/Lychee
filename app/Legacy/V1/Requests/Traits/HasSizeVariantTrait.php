<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

use App\Enum\DownloadVariantType;

trait HasSizeVariantTrait
{
	protected DownloadVariantType $sizeVariant;

	/**
	 * @return DownloadVariantType
	 */
	public function sizeVariant(): DownloadVariantType
	{
		return $this->sizeVariant;
	}
}
