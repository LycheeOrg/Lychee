<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Enum\AspectRatioType;

interface HasAspectRatio
{
	/**
	 * @return AspectRatioType|null
	 */
	public function aspectRatio(): ?AspectRatioType;
}
