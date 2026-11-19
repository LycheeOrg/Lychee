<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Traits\ServesSizeVariantAssetTrait;
use App\Http\Requests\Photo\GetPhotoAssetRequest;
use App\Image\Watermarker;
use Illuminate\Routing\Controller;

/**
 * Serves a single photo size-variant asset (Feature 056, API-056-01).
 */
class PhotoAssetController extends Controller
{
	use ServesSizeVariantAssetTrait;

	public function show(GetPhotoAssetRequest $request, Watermarker $watermarker)
	{
		return $this->serveSizeVariant($request->sizeVariant(), $watermarker);
	}
}
