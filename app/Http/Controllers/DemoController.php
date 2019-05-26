<?php

namespace App\Http\Controllers;

use App\Album;
use App\ControllerFunctions\ReadAccessFunctions;
use App\Metadata\GitHubFunctions;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Photo;

class DemoController extends Controller
{
	public function js()
	{
		$functions = array();

		$configFunctions = new ConfigFunctions();
		$sessionFunctions = new SessionFunctions();
		$githubFunctions = new GitHubFunctions();
		$readAccessFunctions = new ReadAccessFunctions($sessionFunctions);
		$albumFunctions = new AlbumFunctions($readAccessFunctions);

		/**
		 * Session::init.
		 */
		$session_init = new SessionController($configFunctions, $sessionFunctions, $githubFunctions);
		$return_session = array();
		$return_session['name'] = 'Session::init()';
		$return_session['type'] = 'string';
		$return_session['data'] = json_encode($session_init->init());

		$functions[] = $return_session;

		/**
		 * Albums::get.
		 */
		$albums_controller = new AlbumsController($albumFunctions);

		$return_albums = array();
		$return_albums['name'] = 'Albums::get';
		$return_albums['type'] = 'string';
		$return_albums['data'] = json_encode($albums_controller->get());

		$functions[] = $return_albums;

		/**
		 * Album::get.
		 */
		$return_album_list = array();
		$return_album_list['name'] = 'Album::get';
		$return_album_list['type'] = 'array';
		$return_album_list['kind'] = 'albumID';
		$return_album_list['array'] = array();

		$albums = Album::where('public', '=', '1')->where('visible_hidden', '=', '1')->get();
		foreach ($albums as $album) {
			/**
			 * Copy paste from Album::get().
			 */
			$return_album_json = array();
			$return_album_json['albums'] = array();
			// Get photos
			// Get album information
			$return_album_json = $album->prepareData();
			$return_album_json['albums'] = $albumFunctions->get_albums($album);
			$photos_sql = Photo::set_order(Photo::where('album_id', '=', $album->id));

			$previousPhotoID = '';
			$return_album_json['photos'] = array();
			$photo_counter = 0;
			/** @var Photo[] $photos */
			$photos = $photos_sql->get();
			foreach ($photos as $photo_model) {
				// Turn data from the database into a front-end friendly format
				$photo = $photo_model->prepareData();

				// Set previous and next photoID for navigation purposes
				$photo['previousPhoto'] = $previousPhotoID;
				$photo['nextPhoto'] = '';

				// Set current photoID as nextPhoto of previous photo
				if ($previousPhotoID !== '') {
					$return_album_json['photos'][$photo_counter - 1]['nextPhoto'] = $photo['id'];
				}
				$previousPhotoID = $photo['id'];

				// Add to $return_album_json
				$return_album_json['photos'][$photo_counter] = $photo;

				$photo_counter++;
			}

			if ($photos_sql->count() === 0) {
				// Album empty
				$return_album_json['photos'] = false;
			} else {
				// Enable next and previous for the first and last photo
				$lastElement = end($return_album_json['photos']);
				$lastElementId = $lastElement['id'];
				$firstElement = reset($return_album_json['photos']);
				$firstElementId = $firstElement['id'];

				if ($lastElementId !== $firstElementId) {
					$return_album_json['photos'][$photo_counter - 1]['nextPhoto'] = $firstElementId;
					$return_album_json['photos'][0]['previousPhoto'] = $lastElementId;
				}
			}
			$return_album_json['id'] = $album->id;
			$return_album_json['num'] = $photos_sql->count();

			$return_album = array();
			$return_album['id'] = $album->id;
			$return_album['data'] = json_encode($return_album_json);

			$return_album_list['array'][] = $return_album;
		}

		$functions[] = $return_album_list;

		/**
		 * Photo::get.
		 */
		$return_photo_list = array();
		$return_photo_list['name'] = 'Photo::get';
		$return_photo_list['type'] = 'array';
		$return_photo_list['kind'] = 'photoID';
		$return_photo_list['array'] = array();

		$albums = Album::where('public', '=', '1')->where('visible_hidden', '=', '1')->get();
		foreach ($albums as $album) {
			/** @var Photo $photo */
			foreach ($album->photos as $photo) {
				$return_photo = array();
				$return_photo_json = $photo->prepareData();
				$return_photo_json['original_album'] = $return_photo_json['album'];
				$return_photo_json['album'] = $album->id;
				$return_photo['id'] = $photo->id;
				$return_photo['data'] = json_encode($return_photo_json);

				$return_photo_list['array'][] = $return_photo;
			}
		}

		$functions[] = $return_photo_list;

		return view('demo', ['functions' => $functions]);
	}
}
