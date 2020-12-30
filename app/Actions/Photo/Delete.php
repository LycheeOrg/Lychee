<?php

namespace App\Actions\Photo;

use App\Actions\Album\UpdateTakestamps;
use App\Actions\Photo\Extensions\Save;
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
		$photos = Photo::with('album')->whereIn('id', $photoIds)->get();

		$no_error = true;
		$albums = [];

		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();

			if ($photo->album_id !== null) {
				$albums[] = $photo->album;
			}

			$no_error &= $photo->delete();
		}

		// TODO: ideally we would like to avoid duplicates here...
		for ($i = 0; $i < count($albums); $i++) {
			$no_error &= $this->updateTakestamps->singleAndSave($albums[$i]);
		}

		return $no_error;
	}
}
