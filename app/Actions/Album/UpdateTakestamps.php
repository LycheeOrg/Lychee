<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Actions\Album;

use App\Models\Album;

class UpdateTakestamps extends Action
{
	public function single(Album &$album): void
	{
		$album->min_takestamp = $album->get_all_photos()->whereNotNull('takestamp')->min('takestamp');
		$album->max_takestamp = $album->get_all_photos()->whereNotNull('takestamp')->max('takestamp');
	}

	public function singleAndSave(Album &$album): bool
	{
		$this->single($album);

		return $album->save();
	}

	public function ancestors(Album $album)
	{
		if ($album->min_takestamp != null && $album->max_takestamp != null) {
			$album->ancestors()
				->where('min_takestamp', '>=', $album->min_takestamp)
				->orWhere('max_takestamp', '<=', $album->max_takestamp)
				->update(['max_takestamp' => $album->max_takestamp, 'min_takestamp' => $album->min_takestamp]);
		}
	}

	public function all()
	{
		$albums = Album::get();
		foreach ($albums as $_album) {
			$this->singleAndSave($_album);
		}
	}
}
