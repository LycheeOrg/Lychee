<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

use App\Enum\DownloadVariantType;

interface HasSizeVariant
{
	/**
	 * @return DownloadVariantType
	 */
	public function sizeVariant(): DownloadVariantType;
}