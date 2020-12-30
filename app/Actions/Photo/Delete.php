<?php

namespace App\Actions\Photo;

use App\Actions\Album\UpdateTakestamps;
use App\Actions\Photo\Extensions\Save;
use App\Models\Album;
use App\Models\Photo;

class Delete
{
	use Save;

	private $updateTakestamps;

	public function __construct(UpdateTakestamps $updateTakestamps)
	{
		$this->updateTakestamps = $updateTakestamps;
	}

	public function do(array $photoIds)
	{
		$photos = Photo::whereIn('id', $photoIds)->get();

		$no_error = true;
		$albums = Album::whereIn('id', $photos->pluck('album_id'))->get();

		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();
			$no_error &= $photo->delete();
		}

		foreach ($albums as $album) {
			$no_error &= $this->updateTakestamps->singleAndSave($album);
		}

		return $no_error;
	}
}
