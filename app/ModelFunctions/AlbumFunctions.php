<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App\Album;
use App\Logs;
use App\Photo;
use App\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class AlbumFunctions
{

	/**
	 * given an albumID return if the said album is "smart"
	 *
	 * @param $albumID
	 * @return bool
	 */
	public function is_smart_album($albumID)
	{
		if ($albumID === 'f' || $albumID === 's' || $albumID === 'r' || $albumID === 0) {
			return true;
		}
		return false;
	}



	/**
	 * Create a new album from a title and optional parent_id
	 *
	 * @param string $title
	 * @param int $parent_id
	 * @return Album|string
	 */
	public function create(string $title, int $parent_id): Album
	{
		$num = Album::where('id', '=', $parent_id)->count();
		// id cannot be 0, so by definition if $parent_id is 0 then...

		$album = new Album();
		$album->id = Helpers::generateID();
		$album->title = $title;
		$album->description = '';
		$album->owner_id = Session::get('UserID');
		$album->parent_id = ($num == 0 ? null : $parent_id);

		do {
			$retry = false;

			try {
				if (!$album->save()) {
					return Response::error('Could not save album in database!');
				}
			}
			catch (QueryException $e) {
				$errorCode = $e->getCode();
				if ($errorCode == 23000 || $errorCode == 23505) {
					// Duplicate entry
					do {
						usleep(rand(0, 1000000));
						$newId = Helpers::generateID();
					} while ($newId === $album->id);

					$album->id = $newId;
					$retry = true;
				}
				else {
					Logs::error(__METHOD__, __LINE__, 'Something went wrong, error '.$errorCode.', '.$e->getMessage());
					return Response::error('Something went wrong, error'.$errorCode.', please check the logs');
				}
			}
		} while ($retry);

		return $album;
	}



	/**
	 * take a $photo_sql query and return an array containing their pictures.
	 *
	 * @param $photos_sql
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
	 * Given a list of albums, generate an array to be returned
	 *
	 * @param Collection $albums
	 * @return array
	 */
	function prepare_albums(?Collection $albums)
	{

		$return = array();

		if ($albums != null) {
			// For each album
			foreach ($albums as $album_model) {

				// Turn data from the database into a front-end friendly format
				$album = $album_model->prepareData();
				$album['albums'] = $album_model->get_albums();

				// Thumbs
				if ((!Session::get('login') && $album_model->password === null) ||
					(Session::get('login'))) {

					$album['sysstamp'] = $album_model['created_at'];
					$album = $album_model->gen_thumbs($album);
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
	 * @return mixed
	 */
	function genSmartAlbumsThumbs(array $return, Builder $photos_sql, string $kind)
	{
		$photos = $photos_sql->get();
		$i = 0;

		$return[$kind] = array(
			'thumbs'   => array(),
			'thumbs2x' => array(),
			'types'    => array(),
			'num'      => $photos_sql->count()
		);

		foreach ($photos as $photo) {
			if ($i < 3) {
				$return[$kind]['thumbs'][$i] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$photo->thumbUrl;
				if ($photo->thumb2x == '1') {
					$thumbUrl2x = explode(".", $photo->thumbUrl);
					$thumbUrl2x = $thumbUrl2x[0].'@2x.'.$thumbUrl2x[1];
					$return[$kind]['thumbs2x'][$i] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$thumbUrl2x;
				}
				else {
					$return[$kind]['thumbs2x'][$i] = '';
				}
				$return[$kind]['types'][$i] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$photo->type;
				$i++;
			}
			else {
				break;
			}
		}

		return $return;
	}



	/**
	 * @return array|false Returns an array of smart albums or false on failure.
	 */
	function getSmartAlbums()
	{

		/**
		 * Initialize return var
		 */
		$return = array(
			'unsorted' => null,
			'public'   => null,
			'starred'  => null,
			'recent'   => null
		);

		/**
		 * Unsorted
		 */
		$photos_sql = Photo::select_unsorted(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
		$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'unsorted');

		/**
		 * Starred
		 */
		$photos_sql = Photo::select_stars(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
		$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'starred');

		/**
		 * Public
		 */
		$photos_sql = Photo::select_public(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
		$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'public');

		/**
		 * Recent
		 */
		$photos_sql = Photo::select_recent(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl', 'thumb2x', 'type'))->limit(3);
		$return = $this->genSmartAlbumsThumbs($return, $photos_sql, 'recent');

		return $return;

	}


}
