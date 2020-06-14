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
use App\ModelFunctions\PhotoActions\Thumb as Thumb;
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
	 * TODO: MOVE somewhere else
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

	private function get_thumbs_album($album, array $previousThumbIDs): BaseCollection
	{
		$photos = Photo::where('album_id', $album->id)
			->orWhereIn('id', $previousThumbIDs)
			->orderBy('star', 'DESC')
			->orderBy(Configs::get_value('sorting_Photos_col'), Configs::get_value('sorting_Photos_order'))
			->orderBy('id', 'ASC')
			->limit(3)
			->get();

		// php7.4: return $photos->map(fn ($photo) => PhotoCast::toThumb($photo, $this->symLinkFunctions));
		return $photos->map(function ($photo) {
			return PhotoCast::toThumb($photo, $this->symLinkFunctions);
		});
	}

	private function get_thumbs_reduction(Album $album, BaseCollection $previous): BaseCollection
	{
		// php7.4: $previousThumbIDs = $previous
		// php7.4:	->filter(fn ($e) => !$e->isEmpty())
		// php7.4:	->map(fn ($e) => $e[0]->map(fn (Thumb $t) => $t->thumbID))
		// php7.4:	->all();
		$previousThumbIDs = $previous
			->filter(function ($e) {
				return  !$e->isEmpty();
			})->map(function ($e) {
				return $e[0]->map(function (Thumb $t) {
					return $t->thumbID;
				});
			})->all();
		$thumbs = $this->get_thumbs_album($album, $previousThumbIDs);

		return new Collection([$thumbs, $previous]);
	}

	public function get_thumbs(Album $album, BaseCollection $children): BaseCollection
	{
		$reduced = $children->reduce(function ($collection, $child) {
			$reduced_child = $this->get_thumbs($child[0], $child[1]);

			return $collection->push($reduced_child);
		}, new Collection());

		return $this->get_thumbs_reduction($album, $reduced);
	}

	public function set_thumbs(array &$return, BaseCollection $thumbs)
	{
		$return['thumbs'] = [];
		$return['types'] = [];
		$return['thumbs2x'] = [];

		$thumbs[0]->each(function ($thumb, $key) use (&$return) {
			$thumb->insertToArrays($return['thumbs'], $return['types'], $return['thumbs2x']);
		});
	}

	public function set_thumbs_children(array &$return, BaseCollection $thumbs)
	{
		$thumbs->each(function ($thumb, $key) use (&$return) {
			$this->set_thumbs($return[$key], $thumb);
		});
	}

	/**
	 * TODO: MOVE somewhere else.
	 *
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
			foreach ($albums as $album) {
				// Turn data from the database into a front-end friendly format
				$username = null;
				$children = new Collection();

				if ($this->sessionFunctions->is_logged_in()) {
					$username = $album->owner->username;
				}

				if ($this->readAccessFunctions->album($album) === 1) {
					$children = $this->get_children($album, $username);
				}

				$album_array = AlbumCast::toArray($album);
				$album_array['owner'] = $username;
				// php7.4: $album_array['albums'] = $children->map(fn ($e) => AlbumCast::toArray($e[0]));
				$album_array['albums'] = $children->map(function ($e) {
					return AlbumCast::toArray($e[0]);
				});

				$thumbs = $this->get_thumbs($album, $children);
				$this->set_thumbs($album_array, $thumbs);

				// Add to return
				$return[] = $album_array;
			}
		}

		return $return;
	}

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return Collection[int] of all recursive albums ID accessible by the current user from the top level
	 */
	public function getPublicAlbumsId($toplevel = null): BaseCollection
	{
		/*
		 * @var Collection[Album]
		 */
		$toplevel = $toplevel ?? $this->getToplevelAlbums();
		if ($toplevel === null) {
			return null;
		}
		$albumIDs = new Collection();
		$kinds = ['albums', 'shared_albums'];

		foreach ($kinds as $kind) {
			if ($toplevel[$kind] !== null) {
				$toplevel[$kind]->each(function ($album) use (&$albumIDs) {
					if ($this->readAccessFunctions->album($album) === 1) {
						$albumIDs->push($album->id);
						$albumIDs = $albumIDs->concat($this->get_sub_albums_id($album));
					}
				});
			}
		}

		return $albumIDs;
	}

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return array returns an array of smart albums or false on failure
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
		$publicAlbums = null;
		$smartAlbums = [];
		$smartAlbums[] = new UnsortedAlbum($this, $this->sessionFunctions);
		$smartAlbums[] = new StarredAlbum($this, $this->sessionFunctions);
		$smartAlbums[] = new PublicAlbum($this, $this->sessionFunctions);
		$smartAlbums[] = new RecentAlbum($this, $this->sessionFunctions);

		$can_see_smart = $this->sessionFunctions->is_logged_in() && $this->sessionFunctions->can_upload();

		foreach ($smartAlbums as $smartAlbum) {
			if ($can_see_smart || $smartAlbum->is_public()) {
				$publicAlbums = $publicAlbums ?? $this->getPublicAlbumsId($toplevel);
				$smartAlbum->setAlbumIDs($publicAlbums);
				$return[$smartAlbum->get_title()] = [];

				AlbumCast::getThumbs($return[$smartAlbum->get_title()], $smartAlbum, $this->symLinkFunctions);
			}
		}

		return $return;
	}

	/**
	 * Given a query, depending on the sort column, we do it in the query or on the collection.
	 * This is to be able to use natural order sorting on title and descriptions.
	 */
	private function customSort($query, $sortingCol, $sortingOrder)
	{
		if ($query == null) {
			return new Collection();
		}
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
	 * taking advantage of the fact that subalbums inherit parent's owner
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
					} else {
						$_album->content_accessible = false;
					}
					// when we generate the thumbs, we need to know whether that content is visible or not.
					return new Collection([$_album, $collected]);
				}

				return new Collection();
			}
		)->filter(function ($a) {
			return !empty($a);
		});
		// php7.4: )->filter(fn ($a) => !empty($a));
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
	 * this function follows access checks and skips hidden subalbums.
	 * The optional second argument, if true, will result in password-protected
	 * albums being included (but not their content).
	 *
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
	 * Same as above but returns with the album IDs
	 *
	 * Recursively go through each sub album and build a list of them.
	 * Unlike Album::get_all_sub_albums(), this function follows access
	 * checks and skips hidden subalbums.  The optional second argument, if
	 * true, will result in password-protected albums being included (but not
	 * their content).
	 *
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
}
