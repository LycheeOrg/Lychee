<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Actions\Albums\PositionData;
use App\Actions\Albums\Prepare;
use App\Actions\Albums\Smart;
use App\Actions\Albums\Top;
use App\Actions\Albums\Tree;
use App\Models\Configs;

class AlbumsController extends Controller
{
	/**
	 * @return array|string returns an array of albums or false on failure
	 */
	public function get(Top $top, Smart $smart, Prepare $prepareAlbums)
	{
		// caching to avoid further request
		Configs::get();

		// Initialize return var
		$return = [
			'smartalbums' => null,
			'albums' => null,
			'shared_albums' => null,
		];

		// $toplevel containts Collection[Album] accessible at the root: albums shared_albums.
		$toplevel = $top->get();

		$return['albums'] = $prepareAlbums->do($toplevel['albums']);
		$return['shared_albums'] = $prepareAlbums->do($toplevel['shared_albums']);

		$return['smartalbums'] = $smart->get();

		return $return;
	}

	/**
	 * @return array as the full tree of visible albums
	 */
	public function tree(Tree $tree)
	{
		return $tree->get();
	}

	/**
	 * @return array|string returns an array of photos of all albums or false on failure
	 */
	public function getPositionData(PositionData $positionData)
	{
		return $positionData->do();
	}
}
