<?php

namespace App\Actions\Photo;

use App\Models\Photo;

class Delete
{
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
