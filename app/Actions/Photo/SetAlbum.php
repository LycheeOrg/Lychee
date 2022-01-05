<?php

namespace App\Actions\Photo;

use App\Actions\User\Notify;
use App\Models\Album;
use App\Models\Photo;

class SetAlbum extends Setters
{
	public function __construct()
	{
		$this->property = 'album_id';
	}

	public function execute(array $photoIDs, ?string $albumID): bool
	{
		$album = null;
		if ($albumID) {
			$album = Album::query()->findOrFail($albumID);

			foreach ($photoIDs as $id) {
				$photo = Photo::query()->find($id);
				$notify = new Notify();
				$notify->do($photo, $albumID);
			}
		}

		if ($this->do($photoIDs, $albumID)) {
			if ($album) {
				return Photo::query()->whereIn('id', $photoIDs)->update(['owner_id' => $album->owner_id]);
			}

			return true;
		} else {
			return false;
		}
	}
}
