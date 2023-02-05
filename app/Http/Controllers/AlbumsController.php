<?php

namespace App\Http\Controllers;

use App\Actions\Albums\PositionData;
use App\Actions\Albums\Top;
use App\Actions\Albums\Tree;
use App\Contracts\Exceptions\LycheeException;
use App\Http\Resources\Collections\AlbumForestResource;
use App\Http\Resources\Collections\PositionDataResource;
use App\Http\Resources\Collections\TopAlbumsResource;
use App\Models\Configs;
use Illuminate\Routing\Controller;

class AlbumsController extends Controller
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

		return $top->get();
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
