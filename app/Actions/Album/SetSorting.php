<?php

namespace App\Actions\Album;

class SetSorting extends Action
{
	public function do(string $albumID, ?string $sortingCol, ?string $sortingOrder): void
	{
		$album = $this->albumFactory->findModelOrFail($albumID);
		$album->sorting_col = $sortingCol;
		$album->sorting_order = $sortingOrder;
		$album->save();
	}
}
