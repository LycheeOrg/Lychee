<?php

namespace App\Http\Controllers;

use App\Actions\Albums\PositionData;
use App\Actions\Albums\Top;
use App\Actions\Albums\Tree;
use App\Contracts\Exceptions\LycheeException;
use App\DTO\AlbumTree;
use App\DTO\PositionData as PositionDataDTO;
use App\DTO\TopAlbums;
use App\Models\Configs;
use Illuminate\Routing\Controller;

class AlbumsController extends Controller
{
	/**
	 * @return TopAlbums returns the top albums
	 *
	 * @throws LycheeException
	 */
	public function get(Top $top): TopAlbums
	{
		// caching to avoid further request
		Configs::get();

		return $top->get();
	}

	/**
	 * @return AlbumTree the full tree of visible albums
	 *
	 * @throws LycheeException
	 */
	public function tree(Tree $tree): AlbumTree
	{
		return $tree->get();
	}

	/**
	 * @return PositionDataDTO returns visible photos which have positioning data
	 *
	 * @throws LycheeException
	 */
	public function getPositionData(PositionData $positionData): PositionDataDTO
	{
		return $positionData->do();
	}
}
