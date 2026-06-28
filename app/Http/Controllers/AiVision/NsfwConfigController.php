<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Resources\GalleryConfigs\Nsfw\NsfwConfigResource;
use App\Services\Image\NsfwDetectionService;
use Illuminate\Routing\Controller;

class NsfwConfigController extends Controller
{
	public function show(NsfwDetectionService $service): NsfwConfigResource
	{
		return $service->getConfiguration();
	}
}
