<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Events\Metrics\AlbumVisit;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Controllers\MetricsController;
use App\Http\Requests\Album\GetAlbumHeadRequest;
use App\Http\Requests\Traits\HasVisitorIdTrait;
use App\Http\Resources\GalleryConfigs\AlbumConfig;
use App\Http\Resources\Models\HeadAbstractAlbumResource;
use App\Http\Resources\Models\HeadAlbumResource;
use App\Http\Resources\Models\HeadSmartAlbumResource;
use App\Http\Resources\Models\HeadTagAlbumResource;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for returning album metadata without children/photos.
 * Used for pagination - the frontend loads album metadata via this endpoint,
 * then separately fetches paginated children and photos.
 */
class AlbumHeadController extends Controller
{
	use HasVisitorIdTrait;

	/**
	 * Provided an albumID, returns the album metadata without children/photos collections.
	 *
	 * @param GetAlbumHeadRequest $request the request with validated album_id
	 *
	 * @return HeadAbstractAlbumResource album metadata (counts, thumb, rights, config) without children/photos arrays
	 *
	 * @throws LycheeLogicException if album is not a regular Album (Smart/Tag albums not supported)
	 */
	public function get(GetAlbumHeadRequest $request): HeadAbstractAlbumResource
	{
		$config = new AlbumConfig($request->album());
		$album_resource = null;

		$album_resource = match (true) {
			$request->album() instanceof BaseSmartAlbum => new HeadSmartAlbumResource($request->album()),
			$request->album() instanceof TagAlbum => new HeadTagAlbumResource($request->album()),
			$request->album() instanceof Album => new HeadAlbumResource($request->album()),
			// @codeCoverageIgnoreStart
			default => throw new LycheeLogicException('This should not happen'),
			// @codeCoverageIgnoreEnd
		};

		AlbumVisit::dispatchIf((MetricsController::shouldMeasure() && $request->album() instanceof BaseAlbum), $this->visitorId(), $request->album()->get_id());

		return new HeadAbstractAlbumResource($config, $album_resource);
	}
}
