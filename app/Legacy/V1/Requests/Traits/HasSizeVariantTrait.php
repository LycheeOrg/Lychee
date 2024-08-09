<?php

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
