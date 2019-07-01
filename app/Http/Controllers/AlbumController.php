<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Logs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use App\Response;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class AlbumController extends Controller
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
	 * Add a new Album.
	 *
	 * @param Request $request
	 *
	 * @return false|string
	 */
	public function add(Request $request)
	{
		$request->validate([
			'title' => 'string|required|max:100',
			'parent_id' => 'int|nullable',
		]);

		$album = $this->albumFunctions->create($request['title'], $request['parent_id'], $this->sessionFunctions->id());

		return Response::json($album->id, JSON_NUMERIC_CHECK);
	}

	/**
	 * Provided an albumID, returns the album.
	 *
	 * @param Request $request
	 *
	 * @return array|string
	 */
	public function get(Request $request)
	{
		$request->validate(['albumID' => 'string|required']);
		$return = array();
		$return['albums'] = array();
		// Get photos
		// Get album information
		$UserId = $this->sessionFunctions->id();
		$full_photo = Configs::get_value('full_photo', '1') == '1';

		switch ($request['albumID']) {
			case 'f':
				if ($this->sessionFunctions->is_logged_in()) {
					$user = User::find($UserId);

					if ($UserId == 0 || $user->upload) {
						$return['public'] = '0';
						$photos_sql = Photo::select_stars(Photo::OwnedBy($UserId));
						break;
					}
				}
				$return['public'] = '1';
				$photos_sql = Photo::select_stars(Photo::whereIn('album_id', $this->albumFunctions->getPublicAlbums()));
				break;
			case 's':
				$return['public'] = '0';
				$photos_sql = Photo::select_public(Photo::OwnedBy($UserId));
				break;
			case 'r':
				if ($this->sessionFunctions->is_logged_in()) {
					$user = User::find($UserId);

					if ($UserId == 0 || $user->upload) {
						$return['public'] = '0';
						$photos_sql = Photo::select_recent(Photo::OwnedBy($UserId));
						break;
					}
				}
				$return['public'] = '1';
				$photos_sql = Photo::select_recent(Photo::whereIn('album_id', $this->albumFunctions->getPublicAlbums()));
				break;
			case '0':
				$return['public'] = '0';
				$photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId));
				break;
			default:
				$album = Album::with([
					'owner',
					'children',
				])->find($request['albumID']);
				if ($album === null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

					return 'false';
				}
				$return = $album->prepareData();
				// we just require is_logged_in for this one.
				if (!$this->sessionFunctions->is_logged_in()) {
					unset($return['owner']);
				}

				$full_photo = $album->full_photo_visible();
				$return['albums'] = $this->albumFunctions->get_albums($album);
				$photos_sql = Photo::set_order(Photo::where('album_id', '=', $request['albumID']));
				break;
		}

		$return['photos'] = $this->albumFunctions->photos($photos_sql, $full_photo);

		$return['id'] = $request['albumID'];
		$return['num'] = count($return['photos']);

		// finalize the loop
		if ($return['num'] === 0) {
			$return['photos'] = false;
		}

		return $return;
	}

	/**
	 * Provided the albumID and passwords, return whether the album can be accessed or not.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function getPublic(Request $request)
	{
		$request->validate([
			'albumID' => 'string|required',
			'password' => 'string|nullable',
		]);

		switch ($request['albumID']) {
			case 'f':
				return 'false';
			case 's':
				return 'false';
			case 'r':
				return 'false';
			case '0':
				return 'false';
			default:
				$album = Album::find($request['albumID']);
				if ($album === null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

					return 'false';
				}
				if ($album->public == 1) {
					if ($album->password === '') {
						return 'true';
					}
					if ($this->sessionFunctions->has_visible_album($album->id)) {
						return 'true';
					}
					if ($album->checkPassword($request['password'])) {
						$this->sessionFunctions->add_visible_album($album->id);

						return 'true';
					}
				}

				return 'false';
		}
	}

	/**
	 * Provided a title and an albumID, change the title of the album.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setTitle(Request $request)
	{
		$request->validate([
			'albumIDs' => 'string|required',
			'title' => 'string|required|max:100',
		]);

		$albums = Album::whereIn('id', explode(',', $request['albumIDs']))->get();

		if ($albums == null) {
			return 'false';
		}

		$no_error = false;
		foreach ($albums as $album) {
			$album->title = $request['title'];
			$no_error |= $album->save();
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Change the sharing properties of the album.
	 *
	 * @param Request $request
	 *
	 * @return bool|string
	 */
	public function setPublic(Request $request)
	{
		$request->validate([
			'albumID' => 'integer|required',
			'password' => 'string|nullable|max:100',
			'visible' => 'integer|required',
			'downloadable' => 'integer|required',
			'full_photo' => 'integer|required',
		]);

		$album = Album::find($request['albumID']);

		if ($album === null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

			return 'false';
		}

		// Convert values
		$album->full_photo = ($request['full_photo'] === '1' ? 1 : 0);
		$album->public = ($request['public'] === '1' ? 1 : 0);
		$album->visible_hidden = ($request['visible'] === '1' ? 1 : 0);
		$album->downloadable = ($request['downloadable'] === '1' ? 1 : 0);

		// Set public
		if (!$album->save()) {
			return 'false';
		}

		// Reset permissions for photos
		if ($album->public == 1) {
			if ($album->photos()->count() > 0) {
				if (!$album->photos()->update(array('public' => '0'))) {
					return 'false';
				}
			}
		}

		if ($request->has('password')) {
			if (strlen($request['password']) > 0) {
				$album->password = bcrypt($request['password']);
			} else {
				$album->password = null;
			}
			if (!$album->save()) {
				return 'false';
			}
		}

		return 'true';
	}

	/**
	 * Change the description of the album.
	 *
	 * @param Request $request
	 *
	 * @return bool|string
	 */
	public function setDescription(Request $request)
	{
		$request->validate([
			'albumID' => 'integer|required',
			'description' => 'string|nullable|max:1000',
		]);

		$album = Album::find($request['albumID']);

		if ($album === null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

			return 'false';
		}

		$album->description = ($request['description'] == null) ? '' : $request['description'];

		return ($album->save()) ? 'true' : 'false';
	}

	/**
	 * Set the license of the Album.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setLicense(Request $request)
	{
		$request->validate([
			'albumID' => 'required|string',
			'license' => 'required|string',
		]);

		$album = Album::find($request['albumID']);

		if ($album == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

			return 'false';
		}

		$licenses = [
			'none',
			'reserved',
			'CC0',
			'CC-BY',
			'CC-BY-ND',
			'CC-BY-SA',
			'CC-BY-ND',
			'CC-BY-NC-ND',
			'CC-BY-SA',
		];
		$found = false;
		$i = 0;
		while (!$found && $i < count($licenses)) {
			if ($licenses[$i] == $request['license']) {
				$found = true;
			}
			$i++;
		}
		if (!$found) {
			Logs::error(__METHOD__, __LINE__, 'wrong kind of license: ' . $request['license']);

			return Response::error('wrong kind of license!');
		}

		$album->license = $request['license'];

		return $album->save() ? 'true' : 'false';
	}

	/**
	 * Delete the album and all pictures in the album.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function delete(Request $request)
	{
		$request->validate([
			'albumIDs' => 'string|required',
		]);

		$no_error = true;
		if ($request['albumIDs'] == '0') {
			$photos = Photo::select_unsorted(Photo::OwnedBy($this->sessionFunctions->id()))->get();
			foreach ($photos as $photo) {
				$no_error &= $photo->predelete();
				$no_error &= $photo->delete();
			}

			return $no_error ? 'true' : 'false';
		}
		$albums = Album::whereIn('id', explode(',', $request['albumIDs']))->get();

		foreach ($albums as $album) {
			$no_error &= $album->predelete();

			/**
			 * @var Album
			 */
			$parentAlbum = null;
			if ($album->parent_id !== null) {
				$parentAlbum = $album->parent;
				$minTS = $album->min_takestamp;
				$maxTS = $album->max_takestamp;
			}

			$no_error &= $album->delete();

			if ($parentAlbum !== null) {
				$no_error &= $parentAlbum->update_takestamps([$minTS, $maxTS], false);
			}
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Merge albums. The first of the list is the destination of the merge.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function merge(Request $request)
	{
		$request->validate([
			'albumIDs' => 'string|required',
		]);

		// Convert to array
		$albumIDs = explode(',', $request['albumIDs']);
		// Get first albumID
		$albumID = array_shift($albumIDs);

		$album = Album::find($albumID);

		if ($album === null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified albums');

			return 'false';
		}

		$photos = Photo::whereIn('album_id', $albumIDs)->get();
		$no_error = true;
		foreach ($photos as $photo) {
			$photo->album_id = $albumID;

			// just to be sure to handle ownership changes in the process.
			$photo->owner_id = $album->owner_id;

			$no_error &= $photo->save();
		}

		$albums = Album::whereIn('parent_id', $albumIDs)->get();
		$no_error = true;
		foreach ($albums as $album_t) {
			$album_t->parent_id = $albumID;

			// just to be sure to handle ownership changes in the process.
			$album_t->owner_id = $album->owner_id;
			$no_error &= $this->albumFunctions->setContentsOwner($album_t->id, $album->owner_id);

			$no_error &= $album_t->save();
		}
		$no_error &= $album->save();

		$albums = Album::whereIn('id', $albumIDs)->get();
		$takestamps = [];
		foreach ($albums as $album_t) {
			$parentAlbum = null;
			if ($album_t->parent_id !== null) {
				$parentAlbum = $album_t->parent;
				if ($parentAlbum === null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find a parent album');
					$no_error = false;
				}
			}

			array_push($takestamps, $album_t->min_takestamp, $album_t->max_takestamp);

			$no_error &= $album_t->delete();

			if ($parentAlbum !== null) {
				$no_error &= $parentAlbum->update_takestamps(array_slice($takestamps, -2), false);
			}
		}
		$no_error &= $album->update_takestamps($takestamps, true);

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Move multiple albums into another album.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function move(Request $request)
	{
		$request->validate(['albumIDs' => 'string|required']);

		// Convert to array
		$albumIDs = explode(',', $request['albumIDs']);

		// Get first albumID
		$albumID = array_shift($albumIDs);

		$album_master = null;
		if ($albumID != 0) {
			$album_master = Album::find($albumID);
			if ($album_master === null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified albums');

				return 'false';
			}
		}

		$albums = Album::whereIn('id', $albumIDs)->get();
		$no_error = true;
		$takestamps = [];
		foreach ($albums as $album) {
			$oldParentID = $album->parent_id;

			if ($albumID != 0) {
				$album->parent_id = $albumID;

				// just to be sure to handle ownership changes in the process.
				$album->owner_id = $album_master->owner_id;
				$no_error &= $this->albumFunctions->setContentsOwner($album->id, $album_master->owner_id);
			} else {
				$album->parent_id = null;
			}

			$no_error &= $album->save();

			if ($album_master !== null) {
				array_push($takestamps, $album->min_takestamp, $album->max_takestamp);
			}

			if ($oldParentID !== null) {
				$oldParentAlbum = Album::find($oldParentID);
				if ($oldParentAlbum === null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find a parent album');

					$no_error = false;
				}
				$no_error &= $oldParentAlbum->update_takestamps([$album->min_takestamp, $album->max_takestamp], false);
			}
		}
		if ($album_master !== null) {
			$no_error &= $album_master->update_takestamps($takestamps, true);
		}

		return $no_error ? 'true' : 'false';
	}

	/**
	 * Return the archive of the pictures of the album (but not the sub albums !).
	 *
	 * @param Request $request
	 *
	 * @return string|StreamedResponse
	 */
	public function getArchive(Request $request)
	{
		// Illicit chars
		$badChars = array_merge(
			array_map('chr', range(0, 31)),
			array(
				'<',
				'>',
				':',
				'"',
				'/',
				'\\',
				'|',
				'?',
				'*',
			)
		);

		$request->validate([
			'albumID' => 'required|string',
		]);

		$UserId = $this->sessionFunctions->id();
		switch ($request['albumID']) {
			case 'f':
				$zipTitle = 'Starred';
				if ($this->sessionFunctions->is_logged_in()) {
					$user = User::find($UserId);

					if ($UserId == 0 || $user->upload) {
						$photos_sql = Photo::select_stars(Photo::OwnedBy($UserId));
						break;
					}
				}
				$photos_sql = Photo::select_stars(Photo::whereIn('album_id', $this->albumFunctions->getPublicAlbums()));
				break;
			case 's':
				$zipTitle = 'Public';
				$photos_sql = Photo::select_public(Photo::OwnedBy($UserId));
				break;
			case 'r':
				$zipTitle = 'Recent';
				if ($this->sessionFunctions->is_logged_in()) {
					$user = User::find($UserId);

					if ($UserId == 0 || $user->upload) {
						$photos_sql = Photo::select_recent(Photo::OwnedBy($UserId));
						break;
					}
				}
				$photos_sql = Photo::select_recent(Photo::whereIn('album_id', $this->albumFunctions->getPublicAlbums()));
				break;
			case '0':
				$zipTitle = 'Unsorted';
				$photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId));
				break;
			default:
				$album = Album::find($request['albumID']);
				if ($album === null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

					return 'false';
				}
				$zipTitle = $album->title;

				$photos_sql = Photo::set_order(Photo::where('album_id', '=', $request['albumID']));

				Logs::notice(__METHOD__, __LINE__, $album->title . ' has been downloaded.');

				//TODO: we do not provide pictures from sub albums but it would be a nice thing to do later...

//				->orWhereIn('album_id',function ($query) { function ($query) use ($id)
//				{
//					$query->select('album_id')
//						->from('user_album')
//						->where('parent_id','=',$id);
//				}}));
				break;
		}

		$zipTitle = str_replace($badChars, '', $zipTitle) . '.zip';

		$response = new StreamedResponse(function () use ($zipTitle, $photos_sql, $badChars) {
			$opt = array(
				'largeFileSize' => 100 * 1024 * 1024,
				'enableZip64' => true,
				'send_headers' => true,
			);

			$zip = new ZipStream($zipTitle, $opt);

			// Check if album empty
			if ($photos_sql->count() == 0) {
				Logs::error(__METHOD__, __LINE__, 'Could not create ZipStream without images');

				return 'false';
			}

			$photos = $photos_sql->get();
			foreach ($photos as $photo) {
				$title = str_replace($badChars, '', $photo->title);
				$url = Config::get('defines.urls.LYCHEE_URL_UPLOADS_BIG') . $photo->url;

				if (!isset($title) || $title === '') {
					$title = 'Untitled';
				}
				// Check if readable
				if (!@is_readable($url)) {
					Logs::error(__METHOD__, __LINE__, 'Original photo missing: ' . $url);
					continue;
				}

				// Get extension of image
				$extension = Helpers::getExtension($url, false);
				// Set title for photo
				$zipFileName = $zipTitle . '/' . $title . $extension;
				// Check for duplicates
				if (!empty($files)) {
					$i = 1;
					while (in_array($zipFileName, $files)) {
						// Set new title for photo
						$zipFileName = $zipTitle . '/' . $title . '-' . $i . $extension;
						$i++;
					}
				}
				// Add to array
				$files[] = $zipFileName;

				// add a file named 'some_image.jpg' from a local file 'path/to/image.jpg'
				$zip->addFileFromPath($zipFileName, $url);
			}

			// finish the zip stream
			$zip->finish();
		});

		return $response;
	}
}
