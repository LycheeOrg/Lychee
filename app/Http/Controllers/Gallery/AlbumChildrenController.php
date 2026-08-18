<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\DTO\AlbumSortingCriterion;
use App\Http\Requests\Album\GetAlbumChildrenRequest;
use App\Http\Resources\Collections\PaginatedAlbumsResource;
use App\Models\Album;
use App\Models\PersonAlbum;
use App\Models\TagAlbum;
use App\Repositories\AlbumRepository;
use Illuminate\Routing\Controller;

/**
 * Controller for returning paginated child/matching albums.
 * Used by the pagination feature - frontend loads album metadata via /Album::head,
 * then fetches paginated children/matching-albums via this endpoint.
 */
class AlbumChildrenController extends Controller
{
	public function __construct(
		private AlbumRepository $album_repository,
	) {
	}

	/**
	 * Get paginated child (or, for TagAlbum/PersonAlbum, matching) albums.
	 *
	 * @param GetAlbumChildrenRequest $request the request with validated album_id and page
	 *
	 * @return PaginatedAlbumsResource paginated list of albums with metadata
	 */
	public function get(GetAlbumChildrenRequest $request): PaginatedAlbumsResource
	{
		/** @var Album|TagAlbum|PersonAlbum $album */
		$album = $request->album();
		$per_page = $request->configs()->getValueAsInt('albums_per_page');

		if ($album instanceof TagAlbum) {
			$paginator = $this->album_repository->getMatchingAlbumsForTagPaginated($album, $per_page);

			return new PaginatedAlbumsResource($paginator, AlbumSortingCriterion::createDefault(), null);
		}

		if ($album instanceof PersonAlbum) {
			$paginator = $this->album_repository->getMatchingAlbumsForPersonPaginated($album, $per_page);

			return new PaginatedAlbumsResource($paginator, AlbumSortingCriterion::createDefault(), null);
		}

		$sorting_criterion = $album->getEffectiveAlbumSorting();
		$paginator = $this->album_repository->getChildrenPaginated($album->id, $sorting_criterion, $per_page);

		return new PaginatedAlbumsResource($paginator, $sorting_criterion, $album->album_timeline);
	}
}
