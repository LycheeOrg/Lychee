<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Controllers\Traits\ServesSizeVariantAssetTrait;
use App\Http\Requests\Face\GetFacePhotoAssetRequest;
use App\Image\Watermarker;
use Illuminate\Routing\Controller;

/**
 * Serves the photo carrying a face (Feature 093, API-093-05, ADR-093-01),
 * the same way `GET /api/v3/Asset/...` serves a size variant.
 */
class FacePhotoAssetController extends Controller
{
	use ServesSizeVariantAssetTrait;

	public function show(GetFacePhotoAssetRequest $request, Watermarker $watermarker)
	{
		return $this->serveSizeVariant($request->sizeVariant(), $watermarker);
	}
}
