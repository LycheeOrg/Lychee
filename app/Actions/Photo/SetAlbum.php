<?php

namespace App\Actions\Photo;

use App\Actions\User\Notify;
use App\Exceptions\JsonError;
use App\Factories\AlbumFactory;
use App\Models\Photo;

class SetAlbum extends Setters
{
	private $albumFactory;

	public function __construct(AlbumFactory $albumFactory)
	{
		$this->property = 'album_id';
		$this->albumFactory = $albumFactory;
	}

	public function execute(array $photoIDs, string $albumID)
	{
		$album = null;

		if ($albumID != '0') {
			$album = $this->albumFactory->make($albumID);

			if ($album->is_tag_album()) {
				throw new JsonError('Sorry, cannot Set to tag Album.');
			}

			if ($album->is_smart()) {
				throw new JsonError('Sorry, cannot Set to smart Album.');
			}
		}

		foreach ($photoIDs as $id) {
			$photo = Photo::find($id);
			$notify = new Notify();
			$notify->do($photo, $albumID);
		}

		return $this->do($photoIDs, $albumID == '0' ? null : $albumID);
	}
}
