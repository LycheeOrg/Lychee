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
use Illuminate\Database\Query\Builder as QBuilder;
use Illuminate\Database\QueryException;
use Storage;

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
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * AlbumFunctions constructor.
	 *
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(SessionFunctions $sessionFunctions, ReadAccessFunctions $readAccessFunctions, SymLinkFunctions $symLinkFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
		$this->readAccessFunctions = $readAccessFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
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
	 * get the thumbs of an album.
	 *
	 * @param array $return
	 * @param array $album_list
	 */
	public function gen_thumbs(array &$return, $album_list)
	{
		$childThumbIDs = [];

		// As an optimization, we start by extracting the thumbs from the
		// children of this album (which had their thumbs calculated already).
		if (isset($return['albums'])) {
			foreach ($return['albums'] as &$album) {
				$childThumbIDs = array_merge($childThumbIDs, $album['thumbIDs']);
				unset($album['thumbIDs']);
			}
		}

		$photos = Photo::whereIn('album_id', $album_list)
			->orWhereIn('id', $childThumbIDs)
			->orderBy('star', 'DESC')
			->orderBy(Configs::get_value('sorting_Photos_col'), Configs::get_value('sorting_Photos_order'))
			->orderBy('id', 'ASC')
			->limit(3)
			->get();
		// We do not attempt natural sorting here because for large albums
		// it can be many times slower than the SQL sort (since we can't
		// use the "limit" clause).

		// For each thumb
		$k = 0;
		foreach ($photos as $photo) {
			$sym = $this->symLinkFunctions->find($photo);
			if ($sym !== null) {
				$return['thumbs'][$k] = $sym->get('thumbUrl');
				// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
				$return['thumbs2x'][$k] = $sym->get('thumb2x');
			} else {
				$return['thumbs'][$k] = Storage::url('thumb/' . $photo->thumbUrl);
				if ($photo->thumb2x == '1') {
					$thumbUrl2x = explode('.', $photo->thumbUrl);
					$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
					$return['thumbs2x'][$k] = Storage::url('thumb/' . $thumbUrl2x);
				} else {
					$return['thumbs2x'][$k] = '';
				}
			}
			$return['types'][$k] = $photo->type;
			$return['thumbIDs'][$k] = $photo->id;
			$k++;
		}
	}

	/**
	 * take a $photo_sql query and return an array containing their pictures.
	 *
	 * @param Builder $photos_sql
	 * @param bool    $full_photo
	 *
	 * @return array
	 */
	public function photosLocationData(Builder $photos_sql, bool $full_photo)
	{
		$return_photos = [];
		$photo_counter = 0;
		$photos = $photos_sql->select('album_id', 'id', 'latitude', 'longitude', 'small', 'small2x', 'takestamp', 'thumb2x', 'thumbUrl', 'title', 'type', 'url')
						 ->whereNotNull('latitude')
						 ->whereNotNull('longitude')
						 ->with('album')
						 ->get();

		/*
		 * @var Photo
		 */
		foreach ($photos as $photo_model) {
			// Turn data from the database into a front-end friendly format
			$photo = $photo_model->prepareLocationData();
			$this->symLinkFunctions->getUrl($photo_model, $photo);

			// Add to return
			$return_photos[$photo_counter] = $photo;

			$photo_counter++;
		}

		return $return_photos;
	}

	/**
	 * take a $photo_sql query and return an array containing their pictures.
	 *
	 * @param Builder $photos_sql
	 * @param bool    $full_photo
	 *
	 * @return array
	 */
	public function photos(Builder $photos_sql, bool $full_photo)
	{
		$previousPhotoID = '';
		$return_photos = [];
		$photo_counter = 0;
		$photos = $photos_sql->with('album')
			->get();

		$sortingCol = Configs::get_value('sorting_Photos_col');
		if ($sortingCol === 'title' || $sortingCol === 'description') {
			// The result is supposed to be sorted by the user-specified
			// column as the primary key and by 'id' as the secondary key.
			// Unfortunately, sortBy can't be chained the way orderBy can.
			// Instead, we use array_multisort which can be used in a
			// stable fashion, preserving the ordering of elements that
			// compare equal.  We depend here on the collection already
			// being sorted by 'id', via the SQL query.

			// Convert to array so that we can use standard PHP functions.
			$photos = $photos->all();
			// Primary sorting key.
			$values = array_column($photos, $sortingCol);
			// Secondary sorting key -- just preserves current order.
			$keys = array_keys($photos);
			array_multisort($values, Configs::get_value('sorting_Photos_order') === 'ASC' ? SORT_ASC : SORT_DESC, SORT_NATURAL | SORT_FLAG_CASE, $keys, SORT_ASC, $photos);
		}

		/*
		 * @var Photo
		 */
		foreach ($photos as $photo_model) {
			// Turn data from the database into a front-end friendly format
			$photo = $photo_model->prepareData();
			$this->symLinkFunctions->getUrl($photo_model, $photo);
			if (!$this->sessionFunctions->is_current_user($photo_model->owner_id) && !$full_photo) {
				$photo_model->downgrade($photo);
			}

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

		if (count($return_photos) > 0 && Configs::get_value('photos_wraparound', '1') === '1') {
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
	 * @param Collection[Album] $albums
	 *
	 * @return array
	 */
	public function prepare_albums(?Collection $albums)
	{
		$return = [];

		if ($albums != null) {
			// For each album
			/*
			 * @var Album
			 */
			foreach ($albums as $album_model) {
				// Turn data from the database into a front-end friendly format
				$album = $album_model->prepareData();
				$username = null;
				if ($this->sessionFunctions->is_logged_in()) {
					$album['owner'] = $username = $album_model->owner->username;
				}

				if ($this->readAccessFunctions->album($album_model) === 1) {
					$album['albums'] = $this->get_albums($album_model, $username);
					$this->gen_thumbs($album, [$album_model->id]);
				}
				unset($album['thumbIDs']);

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
	 */
	public function genSmartAlbumsThumbs(array &$return, Builder $photos_sql, string $kind)
	{
		$photos = $photos_sql->get();
		$i = 0;

		$return[$kind] = [
			'thumbs' => [],
			'thumbs2x' => [],
			'types' => [],
			'num' => strval($photos_sql->count()),
		];

		/*
		 * @var Photo
		 */
		foreach ($photos as $photo) {
			if ($i < 3) {
				$sym = $this->symLinkFunctions->find($photo);
				if ($sym !== null) {
					$return[$kind]['thumbs'][$i] = $sym->get('thumbUrl');
					// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
					$return[$kind]['thumbs2x'][$i] = $sym->get('thumb2x');
				} else {
					$return[$kind]['thumbs'][$i] = Storage::url('thumb/' . $photo->thumbUrl);
					if ($photo->thumb2x == '1') {
						$thumbUrl2x = explode('.', $photo->thumbUrl);
						$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
						$return[$kind]['thumbs2x'][$i] = Storage::url('thumb/' . $thumbUrl2x);
					} else {
						$return[$kind]['thumbs2x'][$i] = '';
					}
				}
				$return[$kind]['types'][$i] = $photo->type;
				$i++;
			} else {
				break;
			}
		}
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
			/*
			 * @var Album
			 */
			foreach ($toplevel['albums'] as $album) {
				if ($this->readAccessFunctions->album($album) === 1) {
					$albumIDs[] = $album->id;
					$this->get_sub_albums($albumIDs, $album);
				}
			}
		}
		if ($toplevel['shared_albums'] !== null) {
			/*
			 * @var Album
			 */
			foreach ($toplevel['shared_albums'] as $album) {
				if ($this->readAccessFunctions->album($album) === 1) {
					$albumIDs[] = $album->id;
					$this->get_sub_albums($albumIDs, $album);
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
		$return = [
			'unsorted' => null,
			'public' => null,
			'starred' => null,
			'recent' => null,
		];

		if ($this->sessionFunctions->is_logged_in()) {
			$UserId = $this->sessionFunctions->id();

			$user = User::find($UserId);
			if ($UserId == 0 || $user->upload) {
				/**
				 * Unsorted.
				 */
				$photos_sql = Photo::select_unsorted(Photo::OwnedBy($UserId))->limit(3);
				$this->genSmartAlbumsThumbs($return, $photos_sql, 'unsorted');

				/**
				 * Starred.
				 */
				$photos_sql = Photo::select_stars(Photo::OwnedBy($UserId))->limit(3);
				$this->genSmartAlbumsThumbs($return, $photos_sql, 'starred');

				/**
				 * Public.
				 */
				$photos_sql = Photo::select_public(Photo::OwnedBy($UserId))->limit(3);
				$this->genSmartAlbumsThumbs($return, $photos_sql, 'public');

				/**
				 * Recent.
				 */
				$photos_sql = Photo::select_recent(Photo::OwnedBy($UserId))->limit(3);
				$this->genSmartAlbumsThumbs($return, $photos_sql, 'recent');

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
				$photos_sql = Photo::select_stars(Photo::whereIn('album_id', $publicAlbums))->limit(3);
				$this->genSmartAlbumsThumbs($return, $photos_sql, 'starred');
			}

			if (Configs::get_value('public_recent', '0') === '1') {
				/**
				 * Recent.
				 */
				$photos_sql = Photo::select_recent(Photo::whereIn('album_id', $publicAlbums))->limit(3);
				$this->genSmartAlbumsThumbs($return, $photos_sql, 'recent');
			}

			return $return;
		}

		return null;
	}

	/**
	 * Recursively returns the tree structure of albums.
	 *
	 * @param Album $album
	 * @param $username : speed optimization to avoid an extra query,
	 * taking advantage of the fact that subalbums inherit parent's owner
	 * @param $recursionLimit : 0 means infinity
	 *
	 * @return array
	 */
	public function get_albums(Album $album, $username, $recursionLimit = 0): array
	{
		$sortingCol = Configs::get_value('sorting_Albums_col');
		if ($sortingCol !== 'title' && $sortingCol !== 'description') {
			$albums = $album->children()->orderBy($sortingCol, Configs::get_value('sorting_Albums_order'))->get();
		} else {
			$albums = $album->children->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, (Configs::get_value('sorting_Albums_order') === 'DESC'));
		}
		$subAlbums = [];
		foreach ($albums as $subAlbum) {
			$haveAccess = $this->readAccessFunctions->album($subAlbum, true);

			// We do list albums that need a password, but we limit what we
			// return about them.
			if ($haveAccess === 1 || $haveAccess === 3) {
				$subAlbumData = $subAlbum->prepareData();
				if ($username !== null) {
					$subAlbumData['owner'] = $username;
				}

				if ($haveAccess === 1) {
					if ($recursionLimit !== 1) {
						$subAlbumData['albums'] = $this->get_albums($subAlbum, $username, $recursionLimit > 0 ? $recursionLimit - 1 : 0);
						$this->gen_thumbs($subAlbumData, [$subAlbum->id]);
					} else {
						// We will not return the 'albums' data about lower
						// levels.  We still need to descend all the way down
						// to get accurate thumbs info though.
						$subSubAlbums = [$subAlbum->id];
						$this->get_sub_albums($subSubAlbums, $subAlbum);
						$this->gen_thumbs($subAlbumData, $subSubAlbums);
						$subAlbumData['has_albums'] = count($subSubAlbums) > 1 ? '1' : '0';
					}
				}

				$subAlbums[] = $subAlbumData;
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
		/*
		 * @var Photo
		 */
		foreach ($photos as $photo) {
			$photo->owner_id = $ownerId;
			$no_error &= $photo->save();
		}

		$albums = Album::where('parent_id', '=', $albumID)->get();

		/*
		 * @var Album
		 */
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
	 * @param array $return
	 * @param Album $parentAlbum
	 * @param bool  $includePassProtected
	 */
	public function get_sub_albums(array &$return, Album $parentAlbum, $includePassProtected = false)
	{
		/*
		 * @var Album
		 */
		foreach ($parentAlbum->children as $album) {
			$haveAccess = $this->readAccessFunctions->album($album, true);

			if ($haveAccess === 1 || ($includePassProtected && $haveAccess === 3)) {
				$return[] = $album->id;

				if ($haveAccess === 1) {
					$this->get_sub_albums($return, $album, $includePassProtected);
				}
			}
		}
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
		$return = [
			'albums' => null,
			'shared_albums' => null,
		];

		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		$customSort = function ($query) use ($sortingCol, $sortingOrder) {
			if ($sortingCol !== 'title' && $sortingCol !== 'description') {
				return $query
					->orderBy($sortingCol, $sortingOrder)
					->get();
			} else {
				return $query
					->get()
					->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC');
			}
		};

		if ($this->sessionFunctions->is_logged_in()) {
			$id = $this->sessionFunctions->id();
			$user = User::find($id);

			if ($id == 0) {
				$return['albums'] = $customSort(Album::with([
					'owner',
					'children',
				])
					->where('owner_id', '=', 0)
					->where('parent_id', '=', null));

				$return['shared_albums'] = $customSort(Album::with([
					'owner',
					'children',
				])
					->where('owner_id', '<>', 0)
					->where('parent_id', '=', null)
					->orderBy('owner_id', 'ASC'));
			} else {
				if ($user == null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified user (' . $this->sessionFunctions->id() . ')');

					return null;
				} else {
					$return['albums'] = $customSort(Album::with([
						'owner',
						'children',
					])
						->where('owner_id', '=', $user->id)
						->where('parent_id', '=', null));
					$return['shared_albums'] = $customSort($this->get_albums_user($user->id));
				}
			}
		} else {
			$return['albums'] = $customSort(Album::with('children')
				->where('public', '=', '1')
				->where('visible_hidden', '=', '1')
				->where('parent_id', '=', null));
		}

		return $return;
	}

	/**
	 * Given a user, retrieve all the shared albums it can see.
	 *
	 * @param $id
	 *
	 * @return Builder
	 */
	public function get_albums_user($id)
	{
		return Album::with([
			'owner',
			'children',
		])
			->where('owner_id', '<>', $id)
			->where('parent_id', '=', null)
			->where(
				function (Builder $query) use ($id) {
					// album is shared with user
					$query->whereIn('id', function (QBuilder $query) use ($id) {
						$query->select('album_id')
							->from('user_album')
							->where('user_id', '=', $id);
					})
						// or album is visible to user
						->orWhere(
							function (Builder $query) {
								$query->where('public', '=', true)->where('visible_hidden', '=', true);
							});
				})
			->orderBy('owner_id', 'ASC');
	}
}
