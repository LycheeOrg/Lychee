<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Actions\Albums\PositionData;
use App\Actions\Albums\Top;
use App\Contracts\Exceptions\LycheeException;
use App\DTO\TopAlbumDTO;
use App\Http\Resources\Collections\PositionDataResource;
use App\Legacy\V1\Actions\Albums\Tree;
use App\Legacy\V1\Resources\Collections\AlbumForestResource;
use App\Legacy\V1\Resources\Collections\TopAlbumsResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

final class AlbumsController extends Controller
{
	/**
	 * @return TopAlbumsResource returns the top albums
	 *
	 * @throws LycheeException
	 */
	public function get(Top $top): TopAlbumsResource
	{
		// caching to avoid further request
		Configs::get();
		/** @var TopAlbumDTO */
		$dto = $top->get();

		return new TopAlbumsResource(
			smart_albums: $dto->smart_albums,
			tag_albums: $dto->tag_albums,
			albums: $dto->albums,
			shared_albums: $dto->shared_albums);
	}

	/**
	 * @return AlbumForestResource the full tree of visible albums
	 *
	 * @throws LycheeException
	 */
	public function tree(Tree $tree): AlbumForestResource
	{
		return $tree->get();
	}

	/**
	 * @return PositionDataResource returns visible photos which have positioning data
	 *
	 * @throws LycheeException
	 */
	public function getPositionData(PositionData $positionData): PositionDataResource
	{
		return $positionData->do();
	}
}
