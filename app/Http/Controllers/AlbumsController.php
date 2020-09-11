<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\AlbumsFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class AlbumsController extends Controller
{
	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var AlbumsFunctions
	 */
	private $albumsFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @param AlbumFunctions   $albumFunctions
	 * @param AlbumsFunctions  $albumsFunctions
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(
		AlbumFunctions $albumFunctions,
		AlbumsFunctions $albumsFunctions,
		SessionFunctions $sessionFunctions
	) {
		$this->albumFunctions = $albumFunctions;
		$this->albumsFunctions = $albumsFunctions;
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

		// $toplevel containts Collection[Album] accessible at the root: albums shared_albums.
		//
		$toplevel = $this->albumsFunctions->getToplevelAlbums();
		$children = $this->albumsFunctions->get_children($toplevel);

		$return['albums'] = $this->albumsFunctions->prepare_albums($toplevel['albums'], $children['albums']);
		$return['shared_albums'] = $this->albumsFunctions->prepare_albums($toplevel['shared_albums'], $children['shared_albums']);

		$return['smartalbums'] = $this->albumsFunctions->getSmartAlbums($toplevel, $children);

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

		$albumIDs = $this->albumsFunctions->getPublicAlbumsId();

		$query = Photo::with('album')->where(
			function (Builder $query) use ($albumIDs) {
				$query->whereIn('album_id', $albumIDs);
				// Add the 'Unsorted' album.
				if ($this->sessionFunctions->is_logged_in() && $this->sessionFunctions->can_upload()) {
					$query->orWhere('album_id', '=', null);

					$id = $this->sessionFunctions->id();
					if ($id !== 0) {
						$query->where('owner_id', '=', $id);
					}
				}
			}
		);

		$full_photo = Configs::get_value('full_photo', '1') == '1';
		$return['photos'] = $this->albumFunctions->photosLocationData($query, $full_photo);

		return $return;
	}
}
