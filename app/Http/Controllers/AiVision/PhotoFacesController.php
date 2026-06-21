<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Http\Requests\Face\GetPhotoFacesRequest;
use App\Http\Resources\Models\PhotoFacesResource;
use Illuminate\Routing\Controller;

class PhotoFacesController extends Controller
{
	/**
	 * Return the face overlay payload for a single photo.
	 *
	 * GET /Photo/{id}/faces
	 */
	public function show(GetPhotoFacesRequest $request, string $id): PhotoFacesResource
	{
		return new PhotoFacesResource($request->photo());
	}
}
