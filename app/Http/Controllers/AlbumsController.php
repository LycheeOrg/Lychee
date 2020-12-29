<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use AccessControl;
use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Albums\Prepare;
use App\Actions\Albums\Smart;
use App\Actions\Albums\Top;
use App\ModelFunctions\AlbumFunctions;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class AlbumsController extends Controller
{
	use PublicIds;

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @param AlbumFunctions $albumFunctions
	 */
	public function __construct(
		AlbumFunctions $albumFunctions
	) {
		$this->albumFunctions = $albumFunctions;
	}

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
	 * @return array|string returns an array of photos of all albums or false on failure
	 */
	public function getPositionData()
	{
		// caching to avoid further request
		Configs::get();

		// Initialize return var
		$return = [];

		$albumIDs = $this->getPublicAlbumsId();

		$query = Photo::with('album')->where(
			function (Builder $query) use ($albumIDs) {
				$query->whereIn('album_id', $albumIDs);
				// Add the 'Unsorted' album.
				if (AccessControl::is_logged_in() && AccessControl::can_upload()) {
					$query->orWhere('album_id', '=', null);

					$id = AccessControl::id();
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
