<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\ModelFunctions\AlbumsFunctions;
use App\Models\Configs;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
	/**
	 * @var AlbumsFunctions
	 */
	private $albumsFunctions;

	/**
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(
		AlbumsFunctions $albumsFunctions
	) {
		$this->albumsFunctions = $albumsFunctions;
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumid
	 */
	public function album(Request $request, $albumid)
	{
		if ($request['password'] != '') {
			if (Configs::get_value('unlock_password_photos_with_url_param', '0') === '1') {
				$this->albumsFunctions->unlockAllAlbums($request['password']);
			} else {
				$this->albumsFunctions->unlockAlbum($albumid, $request['password']);
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
		if ($request['password'] != '') {
			if (Configs::get_value('unlock_password_photos_with_url_param', '0') === '1') {
				$this->albumsFunctions->unlockAllAlbums($request['password']);
			} else {
				$this->albumsFunctions->unlockAlbum($albumid, $request['password']);
			}
		}

		return redirect('gallery#' . $albumid . '/' . $photoid);
	}
}
