<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Search\AlbumSearch;
use App\Actions\Search\PhotoSearch;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Requests\Search\GetSearchRequest;
use App\Http\Requests\Search\InitSearchRequest;
use App\Http\Resources\Search\InitResource;
use App\Http\Resources\Search\ResultsResource;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for the config.
 */
class SearchController extends Controller
{
	/**
	 * Return init Search.
	 *
	 * @param InitSearchRequest $request
	 *
	 * @return InitResource
	 */
	public function init(InitSearchRequest $request): InitResource
	{
		return new InitResource();
	}

	/**
	 * Get the search results given a query.
	 *
	 * @param GetSearchRequest $request
	 * @param AlbumSearch      $albumSearch
	 * @param PhotoSearch      $photoSearch
	 *
	 * @return ResultsResource
	 */
	public function search(GetSearchRequest $request, AlbumSearch $album_search, PhotoSearch $photo_search): ResultsResource
	{
		$terms = $request->terms();
		$album = $request->album();

		if (!$album instanceof Album) {
			$album = null;
		}

		/** @var LengthAwarePaginator<Photo> $photoResults */
		/** @disregard P1013 Undefined method withQueryString() (stupid intelephense) */
		$photo_results = $photo_search
			->sqlQuery($terms, $album)
			->orderBy(ColumnSortingPhotoType::TAKEN_AT->value, OrderSortingType::ASC->value)
			->paginate(Configs::getValueAsInt('search_pagination_limit'));

		$album_results = $album_search->queryAlbums($terms);

		return ResultsResource::fromData($album_results, $photo_results);
	}
}