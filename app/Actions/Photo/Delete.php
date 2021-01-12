<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Save;
use App\Models\Photo;

class Delete
{
	use Save;

	public function do(array $photoIds)
	{
		$photos = Photo::whereIn('id', $photoIds)->get();

		$no_error = true;

		foreach ($photos as $photo) {
			$no_error &= $photo->predelete();
			$no_error &= $photo->delete();
		}

		return $no_error;
	}
}
