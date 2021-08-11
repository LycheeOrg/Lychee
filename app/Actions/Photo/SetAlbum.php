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

	public function execute(array $photoIDs, string $albumID): bool
	{
		if ($albumID) {
			Album::query()->findOrFail($albumID);

			foreach ($photoIDs as $id) {
				$photo = Photo::query()->find($id);
				$notify = new Notify();
				$notify->do($photo, $albumID);
			}
		}

		return $this->do($photoIDs, $albumID == '0' ? null : $albumID);
	}
}
