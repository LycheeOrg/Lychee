<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App\Album;
use App\Configs;
use App\ControllerFunctions\ReadAccessFunctions;
use App\Logs;
use App\Photo;
use App\Response;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;

class AlbumFunctions
{
	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * AlbumFunctions constructor.
	 *
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param PhotoFunctions      $photoFunctions
	 */
	public function __construct(SessionFunctions $sessionFunctions, ReadAccessFunctions $readAccessFunctions, PhotoFunctions $photoFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
		$this->readAccessFunctions = $readAccessFunctions;
		$this->photoFunctions = $photoFunctions;
	}

	/**
	 * given an albumID return if the said album is "smart".
	 *
	 * @param $albumID
	 *
	 * @return bool
	 */
	public function is_smart_album($albumID)
	{
		if ($albumID === 'f' || $albumID === 's' || $albumID === 'r' || $albumID === '0') {
			return true;
		}

		return false;
	}

	/**
	 * Create a new album from a title and optional parent_id.
	 *
	 * @param string $title
	 * @param int    $parent_id
	 * @param int    $user_id
	 *
	 * @return Album|string
	 */
	public function create(string $title, int $parent_id, int $user_id): Album
	{
		$parent = Album::find($parent_id);
		// we get the parent if it exists.

		$album = new Album();
		$album->id = Helpers::generateID();
		$album->title = $title;
		$album->description = '';

		if ($parent !== null) {
			$album->parent_id = $parent->id;

			// Admin can add subalbums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parent->owner_id;
		} else {
			$album->parent_id = null;
			$album->owner_id = $user_id;
		}

		do {
			$retry = false;

			try {
				if (!$album->save()) {
					return Response::error('Could not save album in database!');
				}
			} catch (QueryException $e) {
				$errorCode = $e->getCode();
				if ($errorCode == 23000 || $errorCode == 23505) {
					// Duplicate entry
					do {
						usleep(rand(0, 1000000));
						$newId = Helpers::generateID();
					} while ($newId === $album->id);

					$album->id = $newId;
					$retry = true;
				} else {
					Logs::error(__METHOD__, __LINE__, 'Something went wrong, error ' . $errorCode . ', ' . $e->getMessage());

					return Response::error('Something went wrong, error' . $errorCode . ', please check the logs');
				}
			}
		} while ($retry);

		return $album;
	}

	/**
	 * take a $photo_sql query and return an array containing their pictures.
	 *
	 * @param $photos_sql
	 *
	 * @return array
	 */
	public function photos(Builder $photos_sql)
	{
		$previousPhotoID = '';
		$return_photos = array();
		$photo_counter = 0;
		$photos = $photos_sql->get();
		foreach ($photos as $photo_model) {
			// Turn data from the database into a front-end friendly format
			$photo = $photo_model->prepareData();
			$this->photoFunctions->getUrl($photo_model, $photo);

			// Set previous and next photoID for navigation purposes
			$photo['previousPhoto'] = $previousPhotoID;
			$photo['nextPhoto'] = '';

			// Set current photoID as nextPhoto of previous photo
			if ($previousPhotoID !== '') {
				$return_photos[$photo_counter - 1]['nextPhoto'] = $photo['id'];
			}
			$previousPhotoID = $photo['id'];

			// Add to return
			$return_photos[$photo_counter] = $photo;

			$photo_counter++;
		}

		if (count($return_photos) > 0) {
			// Enable next and previous for the first and last photo
			$lastElement = end($return_photos);
			$lastElementId = $lastElement['id'];
			$firstElement = reset($return_photos);
			$firstElementId = $firstElement['id'];

			if ($lastElementId !== $firstElementId) {
				$return_photos[$photo_counter - 1]['nextPhoto'] = $firstElementId;
				$return_photos[0]['previousPhoto'] = $lastElementId;
			}
		}

		return $return_photos;
	}

	/**
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @param Collection $albums
	 *
	 * @return array
	 */
	public function prepare_albums(?Collection $albums)
	{
		$return = array();

		if ($albums != null) {
			// For each album
			foreach ($albums as $album_model) {
				// Turn data from the database into a front-end friendly format
				$album = $album_model->prepareData();

				if ($this->readAccessFunctions->album($album_model->id) === 1) {
					$album['albums'] = $this->get_albums($album_model);
					$album = $album_model->gen_thumbs($album, $this->get_sub_albums($album_model, [$album_model->id]));
				}

				// Add to return
				$return[] = $album;
			}
		}

		return $return;
	}

	/**
	 * @param $return
	 * @param $photos_sql
	 * @param $kind
	 *
	 * @return mixed
	 */
	public function genSmartAlbumsThumbs(array $return, Builder $photos_sql, string $kind)
	{
		$photos = $photos_sql->get();
		$i = 0;

		$return[$kind] = array(
			'thumbs' => array(),
			'thumbs2x' => array(),
			'types' => array(),
			'num' => $photos_sql->count(),
		);

		foreach ($photos as $photo) {
			if ($i < 3) {
				$return[$kind]['thumbs'][$i] = Storage::url('thumb/' . $photo->thumbUrl);
				if ($photo->thumb2x == '1') {
					$thumbUrl2x = explode('.', $photo->thumbUrl);
					$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
					$return[$kind]['thumbs2x'][$i] = Storage::url('thumb/' . $thumbUrl2x);
				} else {
					$return[$kind]['thumbs2x'][$i] = '';
				}
				$return[$kind]['types'][$i] = Storage::url('thumb/' . $photo->type);
				$i++;
			} else {
				break;
			}
		}

		return $return;
	}

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return array of all recursive albums accessible by the current user from the top level
	 */
	public function getPublicAlbums($toplevel = null)
	{
		if ($toplevel === null) {
			$toplevel = $this->getToplevelAlbums();
			if ($toplevel === null) {
				return null;
			}
		}

		$albumIDs = [];
		if ($toplevel['albums'] !== null) {
			foreach ($toplevel['albums'] as $album) {
				if ($this->readAccessFunctions->album($album->id) === 1) {
					$albumIDs[] = $album->id;
					$albumIDs = $this->get_sub_albums($album, $albumIDs);
				}
			}
		}
		if ($toplevel['shared_albums'] !== null) {
			foreach ($toplevel['shared_albums'] as $album) {
				if ($this->readAccessFunctions->album($album->id) === 1) {
					$albumIDs[] = $album->id;
					$albumIDs = $this->get_sub_albums($album, $albumIDs);
				}
			}
		}

		return $albumIDs;
	}

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return array|false returns an array of smart albums or false on failure
	 */
	public function getSmartAlbums($toplevel = null)
	{
		/**
		 * Initialize return var.
		 */
		$return = array(
			'unsorted' => null,
			'public' => null,
			'starred' => null,
			'recent' => null,
		);

		if ($this->sessionFunctions->is_logged_in()) {
			$UserId = $this->sessionFunctions->id();

			$user = User::find($UserId);
			if ($UserId == 0 || $user->upload) {
				/**
				 * Unsorted.
				 */
				$photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId)->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
				$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'unsorted');

				/**
				 * Starred.
				 */
				$photos_sql = Photo::select_stars(Photo::OwnedBy($UserId)->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
				$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'starred');

				/**
				 * Public.
				 */
				$photos_sql = Photo::select_public(Photo::OwnedBy($UserId)->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
				$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'public');

				/**
				 * Recent.
				 */
				$photos_sql = Photo::select_recent(Photo::OwnedBy($UserId)->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
				$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'recent');

				return $return;
			}
		}

		if (Configs::get_value('public_starred', '0') === '1' ||
			Configs::get_value('public_recent', '0') === '1') {
			$publicAlbums = $this->getPublicAlbums($toplevel);

			if (Configs::get_value('public_starred', '0') === '1') {
				/**
				 * Starred.
				 */
				$photos_sql = Photo::select_stars(Photo::whereIn('album_id', $publicAlbums))->select('thumbUrl', 'thumb2x', 'type')->limit(3);
				$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'starred');
			}

			if (Configs::get_value('public_recent', '0') === '1') {
				/**
				 * Recent.
				 */
				$photos_sql = Photo::select_recent(Photo::whereIn('album_id', $publicAlbums))->select('thumbUrl', 'thumb2x', 'type')->limit(3);
				$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'recent');
			}

			return $return;
		}

		return null;
	}

	/**
	 * Recursively returns the tree structure of albums.
	 *
	 * @param Album $album
	 *
	 * @return array
	 */
	public function get_albums(Album $album): array
	{
		$subAlbums = [];
		foreach ($album->children as $subAlbum) {
			$haveAccess = $this->readAccessFunctions->album($subAlbum->id, true);

			// We do list albums that need a password, but we limit what we
			// return about them.
			if ($haveAccess === 1 || $haveAccess === 3) {
				$album = $subAlbum->prepareData();
				if (!$this->sessionFunctions->is_logged_in()) {
					unset($album['owner']);
				}

				if ($haveAccess === 1) {
					$album['albums'] = $this->get_albums($subAlbum);
					$album = $subAlbum->gen_thumbs($album, $this->get_sub_albums($subAlbum, [$subAlbum->id]));
				}

				$subAlbums[] = $album;
			}
		}

		return $subAlbums;
	}

	/**
	 * Recursively set the ownership of the contents of an album.
	 *
	 * @param $albumID
	 * @param int $ownerId
	 *
	 * @return bool
	 */
	public function setContentsOwner($albumID, int $ownerId)
	{
		$photos = Photo::where('album_id', '=', $albumID)->get();
		$no_error = true;
		foreach ($photos as $photo) {
			$photo->owner_id = $ownerId;
			$no_error &= $photo->save();
		}

		$albums = Album::where('parent_id', '=', $albumID)->get();
		foreach ($albums as $album) {
			$album->owner_id = $ownerId;
			$no_error &= $album->save();

			$no_error &= $this->setContentsOwner($album->id, $ownerId);
		}

		return $no_error;
	}

	/**
	 * Recursively go through each sub album and build a list of them.
	 * Unlike Album::get_all_sub_albums(), this function follows access
	 * checks and skips hidden subalbums.  The optional third argument, if
	 * true, will result in password-protected albums being included (but not
	 * their content).
	 *
	 * @param $parentAlbum
	 * @param array $return
	 * @param bool  $includePassProtected
	 *
	 * @return array
	 */
	public function get_sub_albums($parentAlbum, $return, $includePassProtected = false)
	{
		foreach ($parentAlbum->children as $album) {
			$haveAccess = $this->readAccessFunctions->album($album->id, true);

			if ($haveAccess === 1 || ($includePassProtected && $haveAccess === 3)) {
				$return[] = $album->id;

				if ($haveAccess === 1) {
					$return = $this->get_sub_albums($album, $return, $includePassProtected);
				}
			}
		}

		return $return;
	}

	/**
	 * Returns an array of top-level albums and shared albums visible to
	 * the current user.
	 * Note: the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array or null
	 */
	public function getToplevelAlbums()
	{
		$return = array(
			'albums' => null,
			'shared_albums' => null,
		);

		if ($this->sessionFunctions->is_logged_in()) {
			$id = $this->sessionFunctions->id();
			$user = User::find($id);

			if ($id == 0) {
				$return['albums'] = Album::where('owner_id', '=', 0)
					->where('parent_id', '=', null)
					->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))->get();
				$return['shared_albums'] = Album::with([
					'owner',
					'children',
				])
					->where('owner_id', '<>', 0)
					->where('parent_id', '=', null)
					->orderBy('owner_id', 'ASC')
					->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))
					->get();
			} else {
				if ($user == null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified user (' . $this->sessionFunctions->id() . ')');

					return null;
				} else {
					$return['albums'] = Album::where('owner_id', '=', $user->id)
						->where('parent_id', '=', null)
						->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))
						->get();
					$return['shared_albums'] = Album::get_albums_user($user->id);
				}
			}
		} else {
			$return['albums'] = Album::where('public', '=', '1')->where('visible_hidden', '=', '1')->where('parent_id', '=', null)
				->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))->get();
		}

		return $return;
	}
}
