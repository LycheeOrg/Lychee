<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Album\PositionData as AlbumPositionData;
use App\Actions\Albums\PositionData as RootPositionData;
use App\Http\Requests\Map\MapDataRequest;
use App\Http\Resources\Collections\PositionDataResource;
use App\Http\Resources\GalleryConfigs\MapProviderData;
use App\Models\Configs;
use Illuminate\Routing\Controller;

class MapController extends Controller
{
	public function __construct(
		protected RootPositionData $root_position_data,
		protected AlbumPositionData $album_position_data,
	) {
	}

	/**
	 * Return the configuration data for the Map.
	 *
	 * @return MapProviderData
	 */
	public function getProvider(): MapProviderData
	{
		return new MapProviderData();
	}

	/**
	 * Return the Map data for an album or root.
	 *
	 * @param MapDataRequest $request
	 *
	 * @return PositionDataResource
	 */
	public function getData(MapDataRequest $request): PositionDataResource
	{
		$album = $request->album();

		if ($album === null) {
			return $this->root_position_data->do();
		}

		$include_sub_albums = Configs::getValueAsBool('map_include_subalbums');

		return $this->album_position_data->get($album, $include_sub_albums);
	}
}