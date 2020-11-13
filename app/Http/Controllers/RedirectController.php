<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\ModelFunctions\AlbumFunctions;
use App\Models\Configs;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(
		AlbumFunctions $albumFunctions
	) {
		$this->albumFunctions = $albumFunctions;
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumid
	 */
	public function album(Request $request, $albumid)
	{
		if ($request->filled('password')) {
			if (Configs::get_value('unlock_password_photos_with_url_param', '0') == '1') {
				$this->albumFunctions->unlockAllAlbums($request['password']);
			} else {
				$this->albumFunctions->unlockAlbum($albumid, $request['password']);
			}
		}

		return redirect('gallery#' . $albumid);
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumid
	 * @param string  $photoid
	 */
	public function photo(Request $request, $albumid, $photoid)
	{
		if ($request->filled('password')) {
			if (Configs::get_value('unlock_password_photos_with_url_param', '0') == '1') {
				$this->albumFunctions->unlockAllAlbums($request['password']);
			} else {
				$this->albumFunctions->unlockAlbum($albumid, $request['password']);
			}
		}

		return redirect('gallery#' . $albumid . '/' . $photoid);
	}
}
