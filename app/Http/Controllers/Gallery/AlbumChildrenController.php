<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Requests\Album\GetAlbumChildrenRequest;
use App\Http\Resources\Collections\PaginatedAlbumsResource;
use App\Repositories\AlbumRepository;
use Illuminate\Routing\Controller;

/**
 * Controller for returning paginated child albums.
 * Used by the pagination feature - frontend loads album metadata via /Album::head,
 * then fetches paginated children via this endpoint.
 */
class AlbumChildrenController extends Controller
{
	public function __construct(
		private AlbumRepository $album_repository,
	) {
	}

	/**
	 * Get paginated child albums.
	 *
	 * @param GetAlbumChildrenRequest $request the request with validated album_id and page
	 *
	 * @return PaginatedAlbumsResource paginated list of child albums with metadata
	 */
	public function get(GetAlbumChildrenRequest $request): PaginatedAlbumsResource
	{
		$album = $request->album();
		$per_page = $request->configs()->getValueAsInt('albums_per_page');
		$sorting_criterion = $album->getEffectiveAlbumSorting();

		$paginator = $this->album_repository->getChildrenPaginated($album->id, $sorting_criterion, $per_page);

		return new PaginatedAlbumsResource($paginator, $album);
	}
}
