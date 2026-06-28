<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Nsfw\GetPhotoNsfwDetectionsRequest;
use App\Http\Resources\Models\PhotoNsfwDetectionsResource;
use Illuminate\Routing\Controller;

class PhotoNsfwDetectionsController extends Controller
{
	/**
	 * Return the NSFW detection overlay payload for a single photo.
	 *
	 * GET /Photo/{id}/nsfw-detections
	 */
	public function show(GetPhotoNsfwDetectionsRequest $request, string $id): PhotoNsfwDetectionsResource
	{
		return new PhotoNsfwDetectionsResource($request->photo());
	}
}
