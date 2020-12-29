<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Actions\Album;

use App\Models\Album;

class UpdateTakestamps
{
	public function single(Album &$album)
	{
		$album->min_takestamp = $album->get_all_photos()->whereNotNull('takestamp')->min('takestamp');
		$album->max_takestamp = $album->get_all_photos()->whereNotNull('takestamp')->max('takestamp');
	}

	public function singleAndSave(Album &$album)
	{
		$this->single($album);
		$album->save();
	}

	public function all()
	{
		$albums = Album::get();
		foreach ($albums as $_album) {
			$this->singleAndSave($_album);
		}
	}
}
