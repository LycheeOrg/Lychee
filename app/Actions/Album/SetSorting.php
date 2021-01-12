<?php

namespace App\Actions\Album;

use App\Models\Logs;

class SetSorting extends Action
{
	public function do(string $albumID, array $value): bool
	{
		if ($this->albumFactory->is_smart($albumID)) {
			Logs::error(__METHOD__, __LINE__, 'Not applicable to smart albums.');

			return false;
		}

		$album = $this->albumFactory->make($albumID);
		$album->sorting_col = $value['typePhotos'] ?? '';
		$album->sorting_order = $value['orderPhotos'] ?? 'ASC';

		return $album->save();
	}
}
