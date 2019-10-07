<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use App\Response;

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
		$return = array(
			'smartalbums' => null,
			'albums' => null,
			'shared_albums' => null,
		);

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
		$return = array();
		$return['albums'] = array();

		// Get photos
		// Get album information
		$UserId = $this->sessionFunctions->id();
		$full_photo = Configs::get_value('full_photo', '1') == '1';

		$toplevel = $this->albumFunctions->getToplevelAlbums();
		if ($toplevel === null) {
			return Response::error('I could not find you.');
		}

		$toplevel_albums = $this->albumFunctions->prepare_albums($toplevel['albums']);
		$toplevel_album_ids = array();
		$album_list = array();

		foreach ($toplevel_albums as $album_iterator) {
			$toplevel_album_ids[] = $album_iterator['id'];

			$album_tmp = Album::with('children')->find($album_iterator['id']);
			$full_photo = $album_tmp->full_photo_visible();

			if ($album_tmp === null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

				return 'false';
			}

			// we just require is_logged_in for this one.
			$username = null;
			if ($this->sessionFunctions->is_logged_in()) {
				$username = $album_iterator['owner'];
			}

			// Get all photos of subalbums -> you only call this function
			// if you want all photos of subalbums
			$album_list = array_merge($album_list, $this->albumFunctions->getAlbumIDsfromAlbumTree($this->albumFunctions->get_albums($album_tmp, $username)));
		}

		$album_list = array_merge($album_list, $toplevel_album_ids);
		$photos_sql = Photo::whereIn('album_id', $album_list);

		$return['photos'] = $this->albumFunctions->photosLocationData($photos_sql, $full_photo);
		$return['id'] = '';

		// Remove all unnecessary data
		unset($return['albums']);
		unset($return['description']);
		unset($return['downloadable']);
		unset($return['full_photo']);
		unset($return['license']);
		unset($return['max_takestamp']);
		unset($return['min_takestamp']);
		unset($return['owner']);
		unset($return['parent_id']);
		unset($return['password']);
		unset($return['public']);
		unset($return['sysdate']);
		unset($return['thumbs']);
		unset($return['thumbs2x']);
		unset($return['thumbIDs']);
		unset($return['types']);
		unset($return['visible']);

		return $return;
	}
}
