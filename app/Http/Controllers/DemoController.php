<?php

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\ControllerFunctions\ReadAccessFunctions;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SessionFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Photo;
use Response;

class DemoController extends Controller
{
	/**
	 * This function returns what are the possible return output to simulate
	 * the server interaction in the case of the demo server here:
	 * https://lycheeorg.github.io/demo/.
	 *
	 * Call /demo and use the generated code to replace the api.post() function
	 *
	 * @return \Illuminate\Http\Response|string
	 */
	public function js()
	{
		if (Configs::get_value('gen_demo_js', '0') != '1') {
			return redirect()->route('home');
		}

		$functions = [];

		$configFunctions = new ConfigFunctions();
		$sessionFunctions = new SessionFunctions();
		$githubFunctions = new GitHubFunctions(new GitRequest());
		$readAccessFunctions = new ReadAccessFunctions($sessionFunctions);
		$symLinkFunctions = new SymLinkFunctions($sessionFunctions);
		$albumFunctions = new AlbumFunctions($sessionFunctions, $readAccessFunctions, $symLinkFunctions);

		/**
		 * Session::init.
		 */
		$session_init = new SessionController($configFunctions, $sessionFunctions, $githubFunctions);
		$return_session = [];
		$return_session['name'] = 'Session::init()';
		$return_session['type'] = 'string';
		$return_session['data'] = json_encode($session_init->init());

		$functions[] = $return_session;

		/**
		 * Albums::get.
		 */
		$albums_controller = new AlbumsController($albumFunctions, $sessionFunctions);

		$return_albums = [];
		$return_albums['name'] = 'Albums::get';
		$return_albums['type'] = 'string';
		$return_albums['data'] = json_encode($albums_controller->get());

		$functions[] = $return_albums;

		/**
		 * Album::get.
		 */
		$return_album_list = [];
		$return_album_list['name'] = 'Album::get';
		$return_album_list['type'] = 'array';
		$return_album_list['kind'] = 'albumID';
		$return_album_list['array'] = [];

		$albums = Album::with('children')
			->where('public', '=', '1')
			->where('visible_hidden', '=', '1')
			->get();
		foreach ($albums as $album) {
			/**
			 * Copy paste from Album::get().
			 */
			// Get photos
			// Get album information
			$return_album_json = $album->prepareData();
			$username = null;
			if ($sessionFunctions->is_logged_in()) {
				$return_album_json['owner'] = $username = $album->owner->username;
			}
			$full_photo = $album->full_photo_visible();
			$return_album_json['albums'] = $albumFunctions->get_albums($album, $username, 1);
			$photos_sql = Photo::set_order(Photo::where('album_id', '=', $album->id));
			foreach ($return_album_json['albums'] as &$alb) {
				unset($alb['thumbIDs']);
			}
			unset($return_album_json['thumbIDs']);

			$previousPhotoID = '';
			$return_album_json['photos'] = [];
			$photo_counter = 0;
			/** @var Photo[] $photos */
			$photos = $photos_sql->with('album')->get();
			foreach ($photos as $photo_model) {
				// Turn data from the database into a front-end friendly format
				$photo = $photo_model->prepareData();
				$symLinkFunctions->getUrl($photo_model, $photo);
				if (!$sessionFunctions->is_current_user($photo_model->owner_id) && !$full_photo) {
					$photo_model->downgrade($photo);
				}

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

			if (count($return_album_json['photos']) === 0) {
				// Album empty
				$return_album_json['photos'] = false;
			} elseif (Configs::get_value('photos_wraparound', '1') === '1') {
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
			$return_album_json['num'] = count($return_album_json['photos']);

			$return_album = [];
			$return_album['id'] = $album->id;
			$return_album['data'] = json_encode($return_album_json);

			$return_album_list['array'][] = $return_album;
		}

		$functions[] = $return_album_list;

		/**
		 * Photo::get.
		 */
		$return_photo_list = [];
		$return_photo_list['name'] = 'Photo::get';
		$return_photo_list['type'] = 'array';
		$return_photo_list['kind'] = 'photoID';
		$return_photo_list['array'] = [];

		$albums = Album::where('public', '=', '1')->where('visible_hidden', '=', '1')->get();
		foreach ($albums as $album) {
			/** @var Photo $photo */
			foreach ($album->photos as $photo) {
				$return_photo = [];
				$return_photo_json = $photo->prepareData();
				$return_photo_json['original_album'] = $return_photo_json['album'];
				$return_photo_json['album'] = $album->id;
				$return_photo['id'] = $photo->id;
				$return_photo['data'] = json_encode($return_photo_json);

				$return_photo_list['array'][] = $return_photo;
			}
		}

		$functions[] = $return_photo_list;

		$contents = view('demo', ['functions' => $functions]);
		$response = Response::make($contents, 200);
		$response->header('Content-Type', 'text/plain');

		return $response;
	}
}
