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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection as BaseCollection;

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

	public function get_thumbs(Album $album, BaseCollection $children): BaseCollection
	{
		$reduced = $children->reduce(function ($collection, $child) {
			$reduced_child = $this->get_thumbs($child[0], $child[1]);

			return $collection->push($reduced_child);
		}, new BaseCollection());

		// php7.4: $previousThumbIDs = $previous
		// php7.4:	->flatMap(fn ($e) => $e[0]->map(fn (Thumb $t) => $t->thumbID))
		// php7.4:	->all();
		$previousThumbIDs = $reduced
			->flatMap(function ($e) {
				return $e[0]->map(function (Thumb $t) {
					return $t->thumbID;
				});
			})->all();
		$thumbs = $this->get_thumbs_album($album, $previousThumbIDs);

		return new Collection([$thumbs, $reduced]);
	}

	public function set_thumbs(array &$return, BaseCollection $thumbs)
	{
		$return['thumbs'] = [];
		$return['types'] = [];
		$return['thumbs2x'] = [];

		$thumbs[0]->each(function (Thumb $thumb, $key) use (&$return) {
			$thumb->insertToArrays($return['thumbs'], $return['types'], $return['thumbs2x']);
		});
	}

	public function set_thumbs_children(BaseCollection &$return, BaseCollection $thumbs)
	{
		$thumbs->each(function (BaseCollection $subthumb, $key) use (&$return) {
			$mod = $return[$key];
			$this->set_thumbs_children($mod['albums'], $subthumb[1]);
			$this->set_thumbs($mod, $subthumb);
			$return[$key] = $mod;
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
			// ! Check if this needs prepareLocationData or to_array
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
	public function photos($photos_sql, bool $full_photo, string $license = 'none')
	{
		$previousPhotoID = '';
		$return_photos = [];
		$photo_counter = 0;
		/**
		 * @var Collection[Photo]
		 */
		$sortingCol = Configs::get_value('sorting_Photos_col');
		$sortingOrder = Configs::get_value('sorting_Photos_order');
		$photos = $this->customSort($photos_sql, $sortingCol, $sortingOrder);

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
			// 	->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'ASC' ? SORT_ASC : SORT_DESC)
			// 	->sortBy('id', SORT_ASC);
			$photos = $photos->all();
			// Primary sorting key.
			$values = array_column($photos, $sortingCol);
			// Secondary sorting key -- just preserves current order.
			$keys = array_keys($photos);
			array_multisort($values, $sortingOrder === 'ASC' ? SORT_ASC : SORT_DESC, SORT_NATURAL | SORT_FLAG_CASE, $keys, SORT_ASC, $photos);
		}

		foreach ($photos as $photo_model) {
			// Turn data from the database into a front-end friendly format
			$photo = PhotoCast::toArray($photo_model);
			PhotoCast::urls($photo, $photo_model);
			PhotoCast::print_license($photo, $license);

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
	 * Given a query, depending on the sort column, we do it in the query or on the collection.
	 * This is to be able to use natural order sorting on title and descriptions.
	 */
	public function customSort($query, $sortingCol, $sortingOrder)
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
	 * ! lots of SQL query.
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
		)->reject(function ($a) {
			return $a->isEmpty();
		});
		// php7.4: )->reject(fn ($a) => $a->isEmpty());
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
		if (!$childrenIDs->isEmpty()) {
			$this->setContentsOwner($childrenIDs, $ownerId);
		}

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
	public function get_sub_albums(Album $album, $includePassProtected = false): BaseCollection
	{
		return $album->children->reduce(function ($collect, $_album) use ($includePassProtected) {
			$haveAccess = $this->readAccessFunctions->album($_album, true);
			if ($haveAccess === 1 || ($includePassProtected && $haveAccess === 3)) {
				if ($haveAccess === 1) {
					$collected = $this->get_sub_albums($_album, $includePassProtected);
				}
				$collect = $collect->push(new BaseCollection([$_album, $collected]));
			}

			return $collect;
		}, new BaseCollection())->reject(function ($e) {
			return $e->isEmpty();
		});
	}
}
