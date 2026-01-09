<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\Album\GetAlbumRequest;
use App\Http\Resources\Models\AlbumHeadResource;
use App\Models\Album;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for returning album metadata without children/photos.
 * Used for pagination - the frontend loads album metadata via this endpoint,
 * then separately fetches paginated children and photos.
 */
class AlbumHeadController extends Controller
{
	/**
	 * Provided an albumID, returns the album metadata without children/photos collections.
	 *
	 * @param GetAlbumRequest $request the request with validated album_id
	 *
	 * @return AlbumHeadResource album metadata (counts, thumb, rights, config) without children/photos arrays
	 *
	 * @throws LycheeLogicException if album is not a regular Album (Smart/Tag albums not supported)
	 */
	public function get(GetAlbumRequest $request): AlbumHeadResource
	{
		$album = $request->album();

		// Only regular albums supported for pagination endpoints
		// Smart albums and Tag albums use existing endpoints with inline pagination
		if (!($album instanceof Album)) {
			throw new LycheeLogicException('AlbumHead endpoint only supports regular albums');
		}

		return new AlbumHeadResource($album);
	}
}
