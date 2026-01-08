<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\Album\GetAlbumPhotosRequest;
use App\Http\Resources\Collections\PaginatedPhotosResource;
use App\Models\Album;
use App\Models\TagAlbum;
use App\Repositories\PhotoRepository;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Routing\Controller;

/**
 * Controller for returning paginated photos.
 * Used by the pagination feature - frontend loads album metadata via /Album::head,
 * then fetches paginated photos via this endpoint.
 */
class AlbumPhotosController extends Controller
{
	public function __construct(private PhotoRepository $photo_repository)
	{
	}

	/**
	 * Get paginated photos.
	 *
	 * @param GetAlbumPhotosRequest $request the request with validated album_id and page
	 *
	 * @return PaginatedPhotosResource paginated list of photos with metadata
	 *
	 * @throws LycheeLogicException if album is not a regular Album (Smart/Tag albums not supported)
	 */
	public function get(GetAlbumPhotosRequest $request): PaginatedPhotosResource
	{
		$album = $request->album();

		$per_page = $request->configs()->getValueAsInt('photos_per_page');

		if ($album instanceof BaseSmartAlbum) {
			/** @disregard P1006 */
			$photos = $album->relationLoaded('photos') ? $album->getPhotos() : null;

			return new PaginatedPhotosResource(
				$photos,
				$album->get_id(),
			);
		}

		if ($album instanceof TagAlbum) {
			$photos = $album->relationLoaded('photos') ? $album->photos : null;

			return new PaginatedPhotosResource(
				$photos,
				$album->id,
				$album->photo_timeline,
			);
		}

		/** @var Album $album */
		$paginator = $this->photo_repository->getPhotosForAlbumPaginated($album->id, $album->getEffectivePhotoSorting(), $per_page);

		return new PaginatedPhotosResource($paginator, $album->id, $album->photo_timeline);
	}
}
