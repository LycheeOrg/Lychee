<?php

namespace App\Actions\Album;

use App\Factories\AlbumFactory;
use App\Models\Album;

class Move extends UpdateTakestamps
{
	/**
	 * @var AlbumFactory
	 */
	public $albumFactory;

	public function __construct(AlbumFactory $albumFactory)
	{
		$this->albumFactory = $albumFactory;
	}

	/**
	 * @param string $albumID
	 *
	 * @return bool
	 */
	public function do(string $albumID, array $albumIDs): bool
	{
		$album_master = null;
		// $albumID = 0 is root
		// ! check type
		if ($albumID != 0) {
			$album_master = $this->albumFactory->make($albumID);
		} else {
			$albumID = null;
		}

		$albums = Album::whereIn('id', $albumIDs)->get();
		$no_error = true;
		$oldParentID = null;

		foreach ($albums as $album) {
			$oldParentID = $album->parent_id;
			$album->parent_id = $albumID;
			$no_error &= $album->save();
		}

		if ($no_error && $oldParentID !== null) {
			$oldParentAlbum = $this->albumFactory->make($oldParentID);
			// update takestamps of old place
			$no_error &= $this->singleAndSave($oldParentAlbum);
		}

		if ($no_error && $album_master !== null) {
			// updat owner
			$album_master->descendants()->update(['owner_id' => $album_master->owner_id]);
			$album_master->get_all_photos()->update(['owner_id' => $album_master->owner_id]);

			// update takestamps parent of new place
			$no_error &= $this->singleAndSave($album_master);
		}

		return $no_error;
	}
}
