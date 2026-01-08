<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Search\AlbumSearch;
use App\Actions\Search\PhotoSearch;
use App\Contracts\Models\AbstractAlbum;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Requests\Search\GetSearchRequest;
use App\Http\Requests\Search\InitSearchRequest;
use App\Http\Resources\Search\InitResource;
use App\Http\Resources\Search\ResultsResource;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Repositories\ConfigManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

/**
 * Controller responsible for the config.
 */
class SearchController extends Controller
{
	/**
	 * Return init Search.
	 */
	public function init(InitSearchRequest $request): InitResource
	{
		return new InitResource();
	}

	/**
	 * Get the search results given a query.
	 */
	public function search(GetSearchRequest $request, AlbumSearch $album_search, PhotoSearch $photo_search): ResultsResource
	{
		$terms = $request->terms();
		$album = $request->album();

		$config_manager = resolve(ConfigManager::class);
		$should_downgrade = $config_manager->getValueAsBool('grants_full_photo_access') === false;

		if (!$album instanceof Album) {
			$album = null;
			$should_downgrade = Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, null]) === false;
		}

		/** @disregard P1013 Undefined method withQueryString() (stupid intelephense) */
		$photo_results = $photo_search
			->sqlQuery($terms, $album)
			->orderBy(ColumnSortingPhotoType::TAKEN_AT->value, OrderSortingType::ASC->value)
			->paginate($request->configs()->getValueAsInt('search_pagination_limit'));

		$album_results = $album_search->queryAlbums($terms, $album);

		return ResultsResource::fromData(
			albums: $album_results,
			photos: $photo_results,
			album_id: $album?->id,
			should_downgrade: $should_downgrade,
		);
	}
}