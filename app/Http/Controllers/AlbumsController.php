<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use App\Response;
use App\User;
use Illuminate\Database\Eloquent\Builder;

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
	 * @param AlbumFunctions   $albumFunctions
	 * @param SessionFunctions $sessionFunctions
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
		$return = [
			'smartalbums' => null,
			'albums' => null,
			'shared_albums' => null,
		];

		$toplevel = $this->albumFunctions->getToplevelAlbums();
		if ($toplevel === null) {
			return Response::error('I could not find you.');
		}

		$return['smartalbums'] = $this->albumFunctions->getSmartAlbums($toplevel);
		$return['albums'] = $this->albumFunctions->prepare_albums($toplevel['albums']);
		$return['shared_albums'] = $this->albumFunctions->prepare_albums($toplevel['shared_albums']);

		return $return;
	}

	/**
	 * @return array|string returns an array of photos of all albums or false on failure
	 */
	public function getPositionData()
	{
		// caching to avoid further request
		Configs::get();

		// Initialize return var
		$return = [];

		$albumIDs = $this->albumFunctions->getPublicAlbums();

		$query = Photo::with('album')->where(
				function (Builder $query) use ($albumIDs) {
					$query->whereIn('album_id', $albumIDs);
					// Add the 'Unsorted' album.
					if ($this->sessionFunctions->is_logged_in()) {
						$id = $this->sessionFunctions->id();
						$user = User::find($id);
						if ($id == 0 || $user->upload) {
							$query->orWhere('album_id', '=', null);
							if ($id !== 0) {
								$query->where('owner_id', '=', $id);
							}
						}
					}
				});

		$full_photo = Configs::get_value('full_photo', '1') == '1';
		$return['photos'] = $this->albumFunctions->photosLocationData($query, $full_photo);

		return $return;
	}
}
