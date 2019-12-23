<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\ControllerFunctions\ReadAccessFunctions;
use App\Logs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use App\Response;
use App\User;
use Illuminate\Http\Request;
use Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
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
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @param AlbumFunctions      $albumFunctions
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 */
	public function __construct(AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions, ReadAccessFunctions $readAccessFunctions)
	{
		$this->albumFunctions = $albumFunctions;
		$this->sessionFunctions = $sessionFunctions;
		$this->readAccessFunctions = $readAccessFunctions;
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
		$return = [];
		$return['albums'] = [];
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
						$return['downloadable'] = '1';
						$photos_sql = Photo::select_stars(Photo::OwnedBy($UserId));
						break;
					}
				}
				$return['public'] = '1';
				$return['downloadable'] = Configs::get_value('downloadable', '0');
				$return['share_button_visible'] = Configs::get_value('share_button_visible', '0');
				$photos_sql = Photo::select_stars(Photo::whereIn('album_id', $this->albumFunctions->getPublicAlbums()));
				break;
			case 's':
				$return['public'] = '0';
				$return['downloadable'] = '1';
				$return['share_button_visible'] = '0';
				$photos_sql = Photo::select_public(Photo::OwnedBy($UserId));
				break;
			case 'r':
				if ($this->sessionFunctions->is_logged_in()) {
					$user = User::find($UserId);

					if ($UserId == 0 || $user->upload) {
						$return['public'] = '0';
						$return['downloadable'] = '1';
						$photos_sql = Photo::select_recent(Photo::OwnedBy($UserId));
						break;
					}
				}
				$return['public'] = '1';
				$return['downloadable'] = Configs::get_value('downloadable', '0');
				$return['share_button_visible'] = Configs::get_value('share_button_visible', '0');
				$photos_sql = Photo::select_recent(Photo::whereIn('album_id', $this->albumFunctions->getPublicAlbums()));
				break;
			case '0':
				$return['public'] = '0';
				$return['downloadable'] = '1';
				$return['share_button_visible'] = '0';
				$photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId));
				break;
			default:
				$album = Album::with('children')->find($request['albumID']);
				if ($album === null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

					return 'false';
				}
				$return = $album->prepareData();
				// we just require is_logged_in for this one.
				$username = null;
				if ($this->sessionFunctions->is_logged_in()) {
					$return['owner'] = $username = $album->owner->username;
				}

				$full_photo = $album->full_photo_visible();
				// To speed things up, we limit subalbum data to at most one
				// level down.
				$return['albums'] = $this->albumFunctions->get_albums($album, $username, 1);
				$photos_sql = Photo::set_order(Photo::where('album_id', '=', $request['albumID']));
				foreach ($return['albums'] as &$alb) {
					unset($alb['thumbIDs']);
				}
				unset($return['thumbIDs']);
				break;
		}

		$return['photos'] = $this->albumFunctions->photos($photos_sql, $full_photo);

		$return['id'] = $request['albumID'];
		$return['num'] = strval(count($return['photos']));

		// finalize the loop
		if ($return['num'] === '0') {
			$return['photos'] = false;
		}

		return $return;
	}

	/**
	 * Provided an albumID, returns the album with only map related data.
	 *
	 * @param Request $request
	 *
	 * @return array|string
	 */
	public function getPositionData(Request $request)
	{
		$request->validate(['albumID' => 'string|required']);
		$request->validate(['includeSubAlbums' => 'string|required']);
		$return = [];
		// Get photos
		// Get album information
		$UserId = $this->sessionFunctions->id();
		$full_photo = Configs::get_value('full_photo', '1') == '1';

		switch ($request['albumID']) {
			case 'f':
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
				$photos_sql = Photo::select_public(Photo::OwnedBy($UserId));
				break;
			case 'r':
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
				$photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId));
				break;
			default:
				$album = Album::with('children')->find($request['albumID']);
				if ($album === null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

					return 'false';
				}

				$full_photo = $album->full_photo_visible();

				$album_list = [];
				if ($request['includeSubAlbums']) {
					// Get all subalbums of the current album
					$this->albumFunctions->get_sub_albums($album_list, $album);
				}

				// Add current albumID to array
				$album_list[] = $request['albumID'];

				$photos_sql = Photo::whereIn('album_id', $album_list);

				break;
		}

		$return['photos'] = $this->albumFunctions->photosLocationData($photos_sql, $full_photo);

		$return['id'] = $request['albumID'];

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
			'public' => 'integer|required',
			'visible' => 'integer|required',
			'downloadable' => 'integer|required',
			'share_button_visible' => 'integer|required',
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
		$album->share_button_visible = ($request['share_button_visible'] === '1' ? 1 : 0);

		// Set public
		if (!$album->save()) {
			return 'false';
		}

		// Reset permissions for photos
		if ($album->public == 1) {
			if ($album->photos()->count() > 0) {
				if (!$album->photos()->update(['public' => '0'])) {
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
	 * Return the archive of the pictures of the album and its subalbums.
	 *
	 * @param Request $request
	 *
	 * @return string|StreamedResponse
	 */
	public function getArchive(Request $request)
	{
		if (Storage::getDefaultDriver() === 's3') {
			Logs::error(__METHOD__, __LINE__, 'getArchive not implemented for S3');

			return 'false';
		}

		// Illicit chars
		$badChars = array_merge(
			array_map('chr', range(0, 31)),
			[
				'<',
				'>',
				':',
				'"',
				'/',
				'\\',
				'|',
				'?',
				'*',
			]
		);

		$request->validate([
			'albumIDs' => 'required|string',
		]);

		$albumIDs = explode(',', $request['albumIDs']);

		if (count($albumIDs) === 1) {
			switch ($albumIDs[0]) {
				case 'f':
					$zipTitle = 'Starred';
					break;
				case 's':
					$zipTitle = 'Public';
					break;
				case 'r':
					$zipTitle = 'Recent';
					break;
				case '0':
					$zipTitle = 'Unsorted';
					break;
				default:
					$album = Album::find($albumIDs[0]);
					if ($album === null) {
						Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

						return 'false';
					}
					$zipTitle = str_replace($badChars, '', $album->title);
					if ($zipTitle === '') {
						$zipTitle = 'Untitled';
					}
					break;
			}
		} else {
			$zipTitle = 'Albums';
		}

		$response = new StreamedResponse(function () use ($albumIDs, $badChars) {
			$options = new \ZipStream\Option\Archive();
			$options->setEnableZip64(Configs::get_value('zip64', '1') === '1');
			$zip = new ZipStream(null, $options);

			$UserId = $this->sessionFunctions->id();

			$dirs = [];
			foreach ($albumIDs as $albumID) {
				$album = null;
				switch ($albumID) {
					case 'f':
						$dir = 'Starred';
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
						$dir = 'Public';
						$photos_sql = Photo::select_public(Photo::OwnedBy($UserId));
						break;
					case 'r':
						$dir = 'Recent';
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
						$dir = 'Unsorted';
						$photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId));
						break;
					default:
						$album = Album::find($albumID);
						if ($album === null) {
							Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

							return 'false';
						}
						$dir = $album->title;
						$photos_sql = Photo::set_order(Photo::where('album_id', '=', $albumID));
						break;
				} // switch (albumID)

				$compress_album = function ($photos_sql, $dir, &$dirs, $parent_dir, $album) use (&$zip, $badChars, &$compress_album) {
					if ($album !== null) {
						if (!$this->sessionFunctions->is_current_user($album->owner_id) &&
						!$album->is_downloadable()) {
							return;
						}
					} else {
						if (!$this->sessionFunctions->is_logged_in() &&
						Configs::get_value('downloadable', '0') === '0') {
							return;
						}
					}

					$dir = str_replace($badChars, '', $dir);
					if ($dir === '') {
						$dir = 'Untitled';
					}
					// Check for duplicates
					if (!empty($dirs)) {
						$i = 1;
						$tmp_dir = $dir;
						while (in_array($tmp_dir, $dirs)) {
							// Set new directory name
							$tmp_dir = $dir . '-' . $i;
							$i++;
						}
						$dir = $tmp_dir;
					}
					$dirs[] = $dir;

					if ($parent_dir !== '') {
						$dir = $parent_dir . '/' . $dir;
					}

					$files = [];
					$photos = $photos_sql->get();
					// We don't bother with additional sorting here; who
					// cares in what order photos are zipped?

					foreach ($photos as $photo) {
						// For photos in public smart albums, skip the ones
						// that are not downloadable based on their actual
						// parent album.
						if ($album === null && !$this->sessionFunctions->is_logged_in() &&
						$photo->album_id !== null && !$photo->album->is_downloadable()) {
							continue;
						}

						$is_raw = ($photo->type == 'raw');

						$prefix_url = $is_raw ? 'raw/' : 'big/';
						$url = Storage::path($prefix_url . $photo->url);
						// Check if readable
						if (!@is_readable($url)) {
							Logs::error(__METHOD__, __LINE__, 'Original photo missing: ' . $url);
							continue;
						}

						// Get extension of image
						$extension = Helpers::getExtension($url, false);

						// Set title for photo
						$title = str_replace($badChars, '', $photo->title);
						if (!isset($title) || $title === '') {
							$title = 'Untitled';
						}

						$file = $title . ($is_raw ? '' : $extension);

						// Check for duplicates
						if (!empty($files)) {
							$i = 1;
							$tmp_file = $file;
							$pos = strrpos($tmp_file, '.');
							while (in_array($tmp_file, $files)) {
								// Set new title for photo
								$tmp_file = substr_replace($file, '-' . $i, $pos, 0);
								$i++;
							}
							$file = $tmp_file;
						}
						// Add to array
						$files[] = $file;

						// Reset the execution timeout for every iteration.
						set_time_limit(ini_get('max_execution_time'));

						// add a file named 'some_image.jpg' from a local file 'path/to/image.jpg'
						$zip->addFileFromPath($dir . '/' . $file, $url);
					} // foreach ($photos)

					// Recursively compress subalbums
					if ($album !== null) {
						$subDirs = [];
						foreach ($album->children as $subAlbum) {
							if ($this->readAccessFunctions->album($subAlbum, true) === 1) {
								$subSql = Photo::set_order(Photo::where('album_id', '=', $subAlbum->id));
								$compress_album($subSql, $subAlbum->title, $subDirs, $dir, $subAlbum);
							}
						}
					}
				}; // $compress_album

				$compress_album($photos_sql, $dir, $dirs, '', $album);
			} // foreach ($albumIDs)

			// finish the zip stream
			$zip->finish();
		});

		// Set file type and destination
		$response->headers->set('Content-Type', 'application/x-zip');
		$disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $zipTitle . '.zip', mb_check_encoding($zipTitle, 'ASCII') ? '' : 'Album.zip');
		$response->headers->set('Content-Disposition', $disposition);

		// Disable caching
		$response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
		$response->headers->set('Pragma', 'no-cache');
		$response->headers->set('Expires', '0');

		return $response;
	}
}
