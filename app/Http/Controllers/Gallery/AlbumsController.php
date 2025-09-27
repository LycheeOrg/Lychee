<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Albums\Top;
use App\Http\Resources\Collections\RootAlbumResource;
use App\Http\Resources\GalleryConfigs\RootConfig;
use App\Models\Configs;
use App\SmartAlbums\UntaggedAlbum;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\Models\UnTaggedSmartAlbumResource;
use App\Http\Resources\Models\ResultsResource;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * Controller responsible for the config.
 */
class AlbumsController extends Controller
{
	/**
	 * Retrieve all the albums at the root.
	 *
     * @return RootAlbumResource|UnTaggedSmartAlbumResource|JsonResponse returns the top albums
	 */
	public function get(Request $request,Top $top): RootAlbumResource|UnTaggedSmartAlbumResource|JsonResponse
	{
		$albumId = $request->get('album_id');
		$page = $request->get('page', 1);
		$perPage = $request->get('per_page', Configs::getValueAsInt('untagged_photos_pagination_limit'));

		if ($albumId === 'untagged') {
			$untaggedAlbum = UntaggedAlbum::getInstance();
			$untaggedAlbum->photos();

            $photos = $untaggedAlbum
                ->photos()
				->paginate($perPage, ['*'], 'page', $page);

			$untaggedResource = UnTaggedSmartAlbumResource::fromData($top->get(), new RootConfig(), $photos);

			return response()->json($untaggedResource->toResponseFormat($request));
		}

		return RootAlbumResource::fromDTO($top->get(), new RootConfig());
	}
}
