<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use AccessControl;
use App\Actions\Album\Cast as AlbumCast;
use App\Actions\ReadAccessFunctions;
use App\Assets\Helpers;
use App\Factories\AlbumFactory;
use App\ModelFunctions\PhotoActions\Cast as PhotoCast;
use App\Models\Album;
use App\Models\Extensions\CustomSort;
use App\Models\Logs;
use App\Models\Photo;
use App\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Hash;

class AlbumFunctions
{
	use CustomSort;

	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @var AlbumFactory
	 */
	private $albumFactory;

	/**
	 * AlbumFunctions constructor.
	 *
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(
		ReadAccessFunctions $readAccessFunctions,
		SymLinkFunctions $symLinkFunctions,
		AlbumFactory $albumFactory
	) {
		$this->readAccessFunctions = $readAccessFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->albumFactory = $albumFactory;
	}

	/**
	 * Create a new smart album based on tags.
	 *
	 * @param string $title
	 * @param string $show_tags
	 * @param int    $user_id
	 *
	 * TODO : MOVE
	 *
	 * @return Album
	 */
	public function createTagAlbum(string $title, string $show_tags, int $user_id): Album
	{
		$album = $this->albumFactory->makeFromTitle($title);

		$album->parent_id = null;
		$album->owner_id = $user_id;

		$album->smart = true;
		$album->showtags = $show_tags;

		return $this->store_album($album);
	}

	/**
	 * Create a new album from a title and optional parent_id.
	 *
	 * @param string $title
	 * @param int    $parent_id
	 * @param int    $user_id
	 *
	 * TODO: MOVE
	 *
	 * @return Album|string
	 */
	public function create(string $title, int $parent_id, int $user_id): Album
	{
		$album = $this->albumFactory->makeFromTitle($title);

		$this->set_parent($album, $parent_id, $user_id);

		return $this->store_album($album);
	}

	/**
	 * Setups parent album on album structure.
	 *
	 * @param Album $album
	 * @param int   $parent_id
	 * @param int   $user_id
	 *
	 * @return Album
	 *
	 * TODO: FIX ME & MOVE
	 */
	private function set_parent(Album $album, int $parent_id, int $user_id): Album
	{
		$parent = Album::find($parent_id);
		// we get the parent if it exists.
		if ($parent !== null) {
			$album->parent_id = $parent->id;

			// Admin can add subalbums to other users' albums.  Make sure that
			// the ownership stays with that user.
			$album->owner_id = $parent->owner_id;
		} else {
			$album->parent_id = null;
			$album->owner_id = $user_id;
		}

		return $album;
	}

	/**
	 * Method that stores new album to the database.
	 *
	 * @param $album
	 *
	 * TODO: MOVE
	 *
	 * @return Album|string
	 */
	private function store_album($album): Album
	{
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

	// public function flatMap_id(BaseCollection $subAlbums): BaseCollection
	// {
	// 	return $subAlbums->reduce(function ($collect, $e) {
	// 		$collect->push($e[0]->id);

	// 		return $collect->concat($this->flatMap_id($e[1]));
	// 	}, new BaseCollection());
	// }

	/**
	 * take a $photo_sql query and return an array containing their pictures.
	 *
	 * @param Builder $photos_sql
	 * @param bool    $full_photo
	 *
	 * TODO: MOVE
	 *
	 * @return array
	 */
	public function photos(Album $album, $photos_sql, bool $full_photo, string $license = 'none')
	{
		[$sortingCol, $sortingOrder] = $album->get_sort();

		$previousPhotoID = '';
		$return_photos = [];
		$photo_counter = 0;
		/**
		 * @var Collection[Photo]
		 */
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
			if (!AccessControl::is_current_user($photo_model->owner_id) && !$full_photo) {
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
	 * Recursively set the ownership of the contents of an album.
	 *
	 * @param $albumID
	 * @param int $ownerId
	 *
	 * TODO: IMPROVE -> Use descendance
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

	public static function is_tag_album(Album $album): bool
	{
		return $album->smart && !empty($album->showtags);
	}

	/**
	 * Provided an password and an album, check if the album can be
	 * unlocked. If yes, unlock all albums with the same password.
	 * ?string is not valid php 7.3 ...
	 *
	 * TODO: MOVE
	 */
	public function unlockAlbum(string $albumid, $password): bool
	{
		switch ($albumid) {
			case 'starred':
			case 'public':
			case 'recent':
			case 'unsorted':
				return false;
			default:
				$album = Album::find($albumid);
				if ($album === null) {
					return false;
				}
				if ($album->public == 1) {
					if ($album->password === '') {
						return true;
					}
					if (AccessControl::has_visible_album($album->id)) {
						return true;
					}
					// $password ??= '';
					$password = $password ?? '';
					if (Hash::check($password, $album->password)) {
						$this->unlockAllAlbums($password);

						return true;
					}
				}

				return false;
		}
	}

	/**
	 * Provided an password, add all the albums that the password unlocks.
	 * TODO: MOVE.
	 */
	public function unlockAllAlbums(string $password): void
	{
		// We add all the albums that the password unlocks so that the
		// user is not repeatedly asked to enter the password as they
		// browse through the hierarchy.  This should be safe as the
		// list of such albums is not exposed to the user and is
		// considered as the last access check criteria.
		$albums = Album::whereNotNull('password')->where('password', '!=', '')->get();
		$albumIDs = [];
		foreach ($albums as $album) {
			if (Hash::check($password, $album->password)) {
				$albumIDs[] = $album->id;
			}
		}

		AccessControl::add_visible_albums($albumIDs);
	}
}
