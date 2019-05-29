<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Logs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Response;
use App\User;
use Illuminate\Support\Facades\Session;

class AlbumsController extends Controller
{
	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @param AlbumFunctions $albumFunctions
	 */
	public function __construct(AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions)
	{
		$this->albumFunctions = $albumFunctions;
		$this->sessionFunctions = $sessionFunctions;
	}

	/**
	 * @return array|string returns an array of albums or false on failure
	 */
	public function get()
	{
		// caching to avoid further request
		Configs::get();

		// Initialize return var
		$return = array(
			'smartalbums' => null,
			'albums' => null,
			'shared_albums' => null,
		);

		$shared_albums = null;

		$toplevel = $this->albumFunctions->getToplevelAlbums();
		if ($toplevel === null) {
			return Response::error('I could not find you.');
		}

		if ($this->sessionFunctions->is_logged_in()) {
			$id = $this->sessionFunctions->id();

			$user = User::find($id);
			if ($id == 0 || $user->upload) {
				$return['smartalbums'] = $this->albumFunctions->getSmartAlbums();
			}
		}

		$return['albums'] = $this->albumFunctions->prepare_albums($toplevel['albums']);
		$return['shared_albums'] = $this->albumFunctions->prepare_albums($toplevel['shared_albums']);

		return $return;
	}
}
