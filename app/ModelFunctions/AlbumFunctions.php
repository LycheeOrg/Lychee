<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App\Album;
use App\Assets\Helpers;
use App\Configs;
use App\ControllerFunctions\ReadAccessFunctions;
use App\Logs;
use App\ModelFunctions\AlbumActions\Cast as AlbumCast;
use App\ModelFunctions\PhotoActions\Cast as PhotoCast;
use App\Photo;
use App\Response;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\RecentAlbum;
use App\SmartAlbums\StarredAlbum;
use App\SmartAlbums\UnsortedAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlbumFunctions
{
	protected $smart_albums = [
		'starred' => '',
		'public' => '',
		'recent' => '',
		'unsorted' => '',
	];
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
		return array_key_exists($albumID, $this->smart_albums);
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

			// Admin can add retSubAlbums to other users' albums.  Make sure that
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

	// public function getAlbum(array &$return, int $albumID)
	// {
	// 	$album = Album::with('children')->find($albumID);
	// 	$return = AlbumCast::toArray($album);
	// 	// we just require is_logged_in for this one.
	// 	$username = null;
	// 	if ($this->sessionFunctions->is_logged_in()) {
	// 		$return['owner'] = $username = $album->owner->username;
	// 	}
	// 	// To speed things up, we limit subalbum data to at most one
	// 	// level down.
	// 	$return['albums'] = $this->get_children($album, $username, 1);
	// 	foreach ($return['albums'] as &$alb) {
	// 		unset($alb['thumbIDs']);
	// 	}
	// 	unset($return['thumbIDs']);

	// 	return Photo::set_order(Photo::where('album_id', '=', $albumID));
	// }

	/**
	 * get the thumbs of an album.
	 *
	 * @param array $return
	 * @param array $album_list
	 */
	public function gen_thumbs(array &$return, $album_list_id = [])
	{
		$childThumbIDs = [];

		// As an optimization, we start by extracting the thumbs from the
		// children of this album (which had their thumbs calculated already).
		if (isset($return['albums'])) {
			foreach ($return['albums'] as &$album) {
				$childThumbIDs = array_merge($childThumbIDs, $album['thumbIDs']);
				// ! does this works ?
				unset($album['thumbIDs']);
			}
		}

		$photos = Photo::whereIn('album_id', $album_list_id)
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
			$ret = PhotoCast::toThumb($photo, $this->symLinkFunctions);
			$return['thumbs'][$k] = $ret['thumbs'];
			$return['types'][$k] = $ret['types'];
			$return['thumbs2x'][$k] = $ret['thumbs2x'];
			$return['thumbIDs'][$k] = $ret['thumbIDs'];
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
	public function photos($photos_sql, bool $full_photo)
	{
		$previousPhotoID = '';
		$return_photos = [];
		$photo_counter = 0;
		/**
		 * @var Collection[Photo]
		 */
		$photos = $photos_sql->get();

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
			// TODO: use collections.
			// * see if this works
			// $photos = $photos
			// 	->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, Configs::get_value('sorting_Photos_order') === 'ASC' ? SORT_ASC : SORT_DESC)
			// 	->sortBy('id', SORT_ASC);
			$photos = $photos->all();
			// Primary sorting key.
			$values = array_column($photos, $sortingCol);
			// Secondary sorting key -- just preserves current order.
			$keys = array_keys($photos);
			array_multisort($values, Configs::get_value('sorting_Photos_order') === 'ASC' ? SORT_ASC : SORT_DESC, SORT_NATURAL | SORT_FLAG_CASE, $keys, SORT_ASC, $photos);
		}

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

		AlbumCast::wrapAroundPhotos($return_photos);

		return $return_photos;
	}

	/**
	 * ? Only used in AlbumsController
	 * TODO: Get rid of full recursion throught the tree of albums
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
			/*
			 * @var Album
			 */
			foreach ($albums as $album_model) {
				// Turn data from the database into a front-end friendly format
				$album_array = AlbumCast::toArray($album_model);
				$username = null;
				if ($this->sessionFunctions->is_logged_in()) {
					$album_array['owner'] = $username = $album_model->owner->username;
				}

				if ($this->readAccessFunctions->album($album_model) === 1) {
					$album_array['albums'] = $this->get_children($album_model, $username);
					// dd($album_array['albums']);
					// $this->gen_thumbs($album_array, [$album_model->id]);
				}
				// unset($album_array['thumbIDs']);

				// Add to return
				$return[] = $album_array;
			}
		}

		return $return;
	}

	/**
	 * @param $return
	 * @param $photos_sql
	 * @param $kind
	 */
	// public function genSmartAlbumsThumbs(array &$return, Builder $photos_sql, string $kind)
	// {
	// 	/**
	// 	 * @var Collection[Photo]
	// 	 */
	// 	$photos = $photos_sql->get();
	// 	$i = 0;

	// 	$return[$kind] = [
	// 		'thumbs' => [],
	// 		'thumbs2x' => [],
	// 		'types' => [],
	// 		'num' => strval($photos_sql->count()),
	// 	];

	// 	/*
	// 	 * @var Photo
	// 	 */
	// 	foreach ($photos as $photo) {
	// 		if ($i < 3) {
	// 			$sym = $this->symLinkFunctions->find($photo);
	// 			if ($sym !== null) {
	// 				$return[$kind]['thumbs'][$i] = $sym->get('thumbUrl');
	// 				// default is '' so if thumb2x does not exist we just reply '' which is the behaviour we want
	// 				$return[$kind]['thumbs2x'][$i] = $sym->get('thumb2x');
	// 			} else {
	// 				$return[$kind]['thumbs'][$i] = Storage::url('thumb/' . $photo->thumbUrl);
	// 				if ($photo->thumb2x == '1') {
	// 					$thumbUrl2x = explode('.', $photo->thumbUrl);
	// 					$thumbUrl2x = $thumbUrl2x[0] . '@2x.' . $thumbUrl2x[1];
	// 					$return[$kind]['thumbs2x'][$i] = Storage::url('thumb/' . $thumbUrl2x);
	// 				} else {
	// 					$return[$kind]['thumbs2x'][$i] = '';
	// 				}
	// 			}
	// 			$return[$kind]['types'][$i] = $photo->type;
	// 			$i++;
	// 		} else {
	// 			break;
	// 		}
	// 	}
	// }

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return Collection[int] of all recursive albums ID accessible by the current user from the top level
	 */
	public function getPublicAlbumsId($toplevel = null)
	{
		/*
		 * @var Collection[Album]
		 */
		$toplevel ??= $this->getToplevelAlbums();
		if ($toplevel === null) {
			return null;
		}

		$albumIDs = new Collection();
		if ($toplevel['albums'] !== null) {
			/*
			 * @var Album
			 */
			foreach ($toplevel['albums'] as $album) {
				if ($this->readAccessFunctions->album($album) === 1) {
					$albumIDs = $albumIDs->concat([$album->id]);
					$albumIDs = $albumIDs->concat($this->get_sub_albums_id($album));
				}
			}
		}
		if ($toplevel['shared_albums'] !== null) {
			/*
			 * @var Album
			 */
			foreach ($toplevel['shared_albums'] as $album) {
				if ($this->readAccessFunctions->album($album) === 1) {
					$albumIDs = $albumIDs->concat([$album->id]);
					$albumIDs = $albumIDs->concat($this->get_sub_albums_id($album));
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
		$return = [];
		/**
		 * @var Collection[SmartAlbum]
		 */
		$smartAlbums = [];
		$smartAlbums[] = new UnsortedAlbum($this, $this->sessionFunctions);
		$smartAlbums[] = new StarredAlbum($this, $this->sessionFunctions);
		$smartAlbums[] = new PublicAlbum($this, $this->sessionFunctions);
		$smartAlbums[] = new RecentAlbum($this, $this->sessionFunctions);

		foreach ($smartAlbums as $smartAlbum) {
			if (
				($this->sessionFunctions->is_logged_in() && $this->sessionFunctions->can_upload())
				|| $smartAlbum->is_public()
			) {
				$return[$smartAlbum->get_title()] = [];
				AlbumCast::getThumbs($return[$smartAlbum->get_title()], $smartAlbum, $this->symLinkFunctions);
				// $photos = $smartAlbum->get_photos()->limit(3)->get();

				// $return[$kind] = [
				// 	'thumbs' => [],
				// 	'thumbs2x' => [],
				// 	'types' => [],
				// 	'num' => strval($photos_sql->count()),
				// ];
				// foreach ($photos as $photo) {

				// $this->genSmartAlbumsThumbs($return, $photos_sql, $smartAlbum->get_title());
			}
		}

		return $return ?? null;
	}

	/**
	 * Given a query, depending of the sort collumn, we do it in the query or on the collection.
	 * This is to be able to use natural order sorting on title and descriptions.
	 */
	private function customSort($query, $sortingCol, $sortingOrder)
	{
		if ($query == null) {
			return new Collection();
		}
		// dd($query);
		if (!in_array($sortingCol, ['title', 'description'])) {
			return $query
				->orderBy($sortingCol, $sortingOrder)
				->get();
		} else {
			return $query
				->get()
				->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC');
		}
	}

	/**
	 * ! may be memory intensive
	 * TODO: Add recursion limit back.
	 *
	 * Recursively returns the tree structure of albums.
	 *
	 * @param Album $album
	 * @param $username : speed optimization to avoid an extra query,
	 * taking advantage of the fact that retSubAlbums inherit parent's owner
	 * @param $recursionLimit : 0 means infinity
	 *
	 * @return Collection
	 */
	public function get_children(Album $album, $recursionLimit = 0, $includePassProtected = false): BaseCollection
	{
		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');
		$children = $this->customSort($album->children(), $sortingCol, $sortingOrder);

		return $children->map(
			function ($_album) use ($includePassProtected) {
				$haveAccess = $this->readAccessFunctions->album($_album, true);

				if ($haveAccess === 1 || ($includePassProtected && $haveAccess === 3)) {
					if ($haveAccess === 1) {
						$collected = $this->get_sub_albums($_album, $includePassProtected);
					}

					return new Collection([$_album, $collected]);
				}

				return new Collection();
			}
		);

		// we have all the accessible children.

		// dd($children);
		// $retSubAlbums = new Collection();
		// foreach ($children as $subAlbum) {
		// 	$haveAccess = $this->readAccessFunctions->album($subAlbum, true);

		// 	// We do list albums that need a password, but we limit what we
		// 	// return about them.
		// 	if ($haveAccess === 1 || $haveAccess === 3) {
		// 		// do we need that ?
		// 		$subAlbumData = AlbumCast::toArray($subAlbum);
		// 		if ($username !== null) {
		// 			$subAlbumData['owner'] = $username;
		// 		}

		// 		if ($haveAccess === 1) {
		// 			if ($recursionLimit !== 1) {
		// 				$subAlbumData['albums'] = $this->get_children($subAlbum, $username, $recursionLimit > 0 ? $recursionLimit - 1 : 0)->all();
		// 				$this->gen_thumbs($subAlbumData, [$subAlbum->id]);
		// 			} else {
		// 				// We will not return the 'albums' data about lower
		// 				// levels.  We still need to descend all the way down
		// 				// to get accurate thumbs info though.
		// 				$subretSubAlbums = new Collection([$subAlbum->id]);
		// 				$this->get_sub_albums_id($subAlbum);
		// 				$this->gen_thumbs($subAlbumData, $subretSubAlbums);
		// 				$subAlbumData['has_albums'] = count($subretSubAlbums) > 1 ? '1' : '0';
		// 			}
		// 		}

		// 		$retSubAlbums[] = $subAlbumData;
		// 	}
		// }

		// return $retSubAlbums;
	}

	/**
	 * Recursively set the ownership of the contents of an album.
	 *
	 * @param $albumID
	 * @param int $ownerId
	 *
	 * @return bool
	 */
	public function setContentsOwner($albumIDs, int $ownerId)
	{
		Photo::whereIn('album_id', $albumIDs)->update(['owner_id' => $ownerId]);
		Album::whereIn('parent_id', $albumIDs)->update(['owner_id' => $ownerId]);

		$childrenIDs = Album::select('id')->whereIn('parent_id', $albumIDs)->get();
		$this->setContentsOwner($childrenIDs, $ownerId);

		return true;
	}

	/**
	 * ! Memory intensive
	 * ! lots of SQL query.
	 *
	 * Recursively go through each sub album and build a list of them.
	 * Unlike AlbumActions\UpdateTakestamps::get_all_sub_albums_id(),
	 * this function follows access checks and skips hidden retSubAlbums.
	 * The optional third argument, if true, will result in password-protected
	 * albums being included (but not their content).
	 *
	 * @param array $return
	 * @param Album $parentAlbum
	 * @param bool  $includePassProtected
	 *
	 * @return Collection[Album]
	 */
	public function get_sub_albums(Album $album, $includePassProtected = false): ?BaseCollection
	{
		return $album->children->reduce(function ($collect, $_album) use ($includePassProtected) {
			$haveAccess = $this->readAccessFunctions->album($_album, true);
			if ($haveAccess === 1 || ($includePassProtected && $haveAccess === 3)) {
				if ($haveAccess === 1) {
					$collected = $this->get_sub_albums($_album, $includePassProtected);
				}
				$collect = $collect->concat([$_album, $collected]);
			}
		}, new Collection());
	}

	/**
	 * ! Memory intensive.
	 *
	 * Same as above but only with the ID
	 *
	 * Recursively go through each sub album and build a list of them.
	 * Unlike Album::get_all_sub_albums(), this function follows access
	 * checks and skips hidden retSubAlbums.  The optional third argument, if
	 * true, will result in password-protected albums being included (but not
	 * their content).
	 *
	 * @param array $return
	 * @param Album $parentAlbum
	 * @param bool  $includePassProtected
	 *
	 * @return Collection[int]
	 */
	public function get_sub_albums_id(Album $album, $includePassProtected = false): Collection
	{
		return $this->get_sub_albums($album, $includePassProtected)->map(function ($album) {
			return $album->id;
		});
	}

	/**
	 * Returns an array of top-level albums and shared albums visible to
	 * the current user.
	 * Note: the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array[Collection[Album]]
	 */
	public function getToplevelAlbums(): array
	{
		$return = [
			'albums' => null,
			'shared_albums' => null,
		];

		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		if ($this->sessionFunctions->is_logged_in()) {
			$sql = Album::with([
				'owner',
			])->where('parent_id', '=', null);

			$id = $this->sessionFunctions->id();

			if ($id > 0) {
				$shared = $this->get_shared_album($id);

				$sql = $sql->where(function ($query) use ($id, $shared) {
					$query = $query->where('owner_id', '=', $id);
					$query = $query->orWhereIn('id', $shared);
					$query = $query->orWhere(
						$query->where('public', '=', true)->where('visible_hidden', '=', true)
					);
				});
			}

			$sql = $sql->orderBy('owner_id', 'ASC');

			$albumCollection = $this->customSort($sql, $sortingCol, $sortingOrder);

			list($return['albums'], $return['shared_albums']) = $albumCollection->partition(function ($album) use ($id) {
				return $album->owner_id == $id;
			});
		} else {
			$return['albums'] = $this->customSort(Album::where('public', '=', '1')
				->where('visible_hidden', '=', '1')
				->where('parent_id', '=', null), $sortingCol, $sortingOrder);
		}

		return $return;
	}

	private function get_shared_album($id)
	{
		return DB::select('album_id')
			->from('user_album')
			->where('user_id', '=', $id)->get();
	}

	// /**
	//  * Given a user, retrieve all the shared albums it can see.
	//  *
	//  * @param $id
	//  *
	//  * @return Builder
	//  */
	// public function get_children_user($id)
	// {
	// 	return Album::with([
	// 		'owner',
	// 		'children',
	// 	])
	// 		->where('owner_id', '<>', $id)
	// 		->where('parent_id', '=', null)
	// 		->where(
	// 			function (Builder $query) use ($id) {
	// 				// album is shared with user
	// 				$query->whereIn('id', $this->get_shared_album($id))
	// 					// or album is visible to user
	// 					->orWhere(
	// 						function (Builder $query) {
	// 							$query->where('public', '=', true)->where('visible_hidden', '=', true);
	// 						}
	// 					);
	// 			}
	// 		)
	// 		->orderBy('owner_id', 'ASC');
	// }
}
