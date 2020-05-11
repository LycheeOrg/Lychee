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
use Spatie\Feed\FeedItem;

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

	public function getRSS()
	{
		return [];
		$photo = Photo::where('created_at', '>=', Carbon::now()->subDays(intval(Configs::get_value('recent_age', '1')))->toDateTimeString())
		->where(function ($q) {
			$q->whereIn('album_id',
				$this->albumFunctions->getPublicAlbums())
				->orWhere('public', '=', '1');
		})->all();

		if ($photo->album_id != null) {
			$album = $photo->album;
			if (!$album->full_photo_visible()) {
				$photo->downgrade($return);
			}
			$return['downloadable'] = $album->is_downloadable() ? '1' : '0';
			$return['share_button_visible'] = $album->is_share_button_visible() ? '1' : '0';
		} else { // Unsorted
			if (Configs::get_value('full_photo', '1') != '1') {
				$photo->downgrade($return);
			}
			$return['downloadable'] = Configs::get_value('downloadable', '0');
			$return['share_button_visible'] = Configs::get_value('share_button_visible', '0');
		}

		FeedItem::create();
	}
}
