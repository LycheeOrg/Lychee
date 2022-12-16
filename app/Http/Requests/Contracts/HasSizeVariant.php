<?php

namespace App\Http\Requests\Contracts;

use App\Enum\DownloadVariantType;

interface HasSizeVariant
{
	/**
	 * @return DownloadVariantType
	 */
	public function sizeVariant(): DownloadVariantType;
}