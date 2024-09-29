<?php

namespace App\Contracts\Http\Requests;

use App\Enum\DownloadVariantType;

interface HasSizeVariant
{
	/**
	 * @return ?DownloadVariantType
	 */
	public function sizeVariant(): ?DownloadVariantType;
}