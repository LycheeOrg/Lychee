<?php

namespace App\Actions\Album;

class SetSorting extends Action
{
	public function do(string $albumID, array $value): bool
	{
		$album = $this->albumFactory->findModelOrFail($albumID);
		$album->sorting_col = $value['typePhotos'] ?? '';
		$album->sorting_order = $value['orderPhotos'] ?? 'ASC';

		return $album->save();
	}
}
