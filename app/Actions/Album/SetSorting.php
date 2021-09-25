<?php

namespace App\Actions\Album;

use App\Exceptions\ModelDBException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SetSorting extends Action
{
	/**
	 * @throws ModelDBException
	 * @throws ModelNotFoundException
	 */
	public function do(string $albumID, ?string $sortingCol, ?string $sortingOrder): void
	{
		$album = $this->albumFactory->findModelOrFail($albumID);
		$album->sorting_col = $sortingCol;
		$album->sorting_order = $sortingOrder;
		$album->save();
	}
}
