<?php

namespace App\Http\Requests\Contracts;

use App\Enum\DonwloadVariantType;

interface HasSizeVariant
{
	/**
	 * @return DonwloadVariantType
	 */
	public function sizeVariant(): DonwloadVariantType;
}