<?php

namespace App\Http\Requests\Traits;

use App\Enum\DonwloadVariantType;

trait HasSizeVariantTrait
{
	protected DonwloadVariantType $sizeVariant;

	/**
	 * @return DonwloadVariantType
	 */
	public function sizeVariant(): DonwloadVariantType
	{
		return $this->sizeVariant;
	}
}
