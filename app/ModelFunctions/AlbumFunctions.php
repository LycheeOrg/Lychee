<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use AccessControl;
use App\Actions\ReadAccessFunctions;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\Extensions\CustomSort;
use Illuminate\Database\Eloquent\Builder;
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
	 * Recursively set the ownership of the contents of an album.
	 *
	 * @param $albumID
	 * @param int $ownerId
	 *
	 * @return bool
	 */
	public function setContentsOwner($albumIDs, int $ownerId)
	{
		$album = Album::find($albumIDs);
		$album->descendants()->update(['owner_id' => $ownerId]);
		$album->get_all_photos()->update(['owner_id' => $ownerId]);

		return true;
	}

	/**
	 * Provided an password and an album, check if the album can be
	 * unlocked. If yes, unlock all albums with the same password.
	 *
	 * TODO: MOVE
	 */
	public function unlockAlbum(?string $albumid, $password): bool
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
					$password ??= '';
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
