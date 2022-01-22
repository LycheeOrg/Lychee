<?php

namespace App\Http\Controllers;

use App\Actions\Albums\PositionData;
use App\Actions\Albums\Smart;
use App\Actions\Albums\Top;
use App\Actions\Albums\Tree;
use App\Models\Configs;

class AlbumsController extends Controller
{
	/**
	 * @return array returns an array of albums or false on failure
	 */
	public function get(Top $top, Smart $smart): array
	{
		// caching to avoid further request
		Configs::get();

		// Initialize return var
		$return = [
			'smart_albums' => [],
			'albums' => null,
			'shared_albums' => null,
		];

		// $toplevel contains Collection<Album> accessible at the root: albums shared_albums.
		$toplevel = $top->get();

		$return['albums'] = $toplevel['albums'];
		$return['shared_albums'] = $toplevel['shared_albums'];
		// TODO: We may want to refactor this in the front end so this "cast" is no longer necessary.
		$smart->get()->each(function ($e) use (&$return) { $return['smart_albums'][$e->id] = $e; });

		return $return;
	}

	/**
	 * @return array as the full tree of visible albums
	 */
	public function tree(Tree $tree): array
	{
		return $tree->get();
	}

	/**
	 * @return array returns an array of visible photos which have positioning data
	 */
	public function getPositionData(PositionData $positionData): array
	{
		return $positionData->do();
	}
}
