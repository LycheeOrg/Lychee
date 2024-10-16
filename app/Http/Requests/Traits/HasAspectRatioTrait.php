<?php

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
