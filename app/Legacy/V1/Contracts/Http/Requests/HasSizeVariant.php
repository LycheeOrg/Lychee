<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

use App\Enum\DownloadVariantType;

interface HasSizeVariant
{
	/**
	 * @return DownloadVariantType
	 */
	public function sizeVariant(): DownloadVariantType;
}