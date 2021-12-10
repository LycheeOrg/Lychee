<?php

namespace App\Actions\Photo;

use App\Exceptions\ModelDBException;
use App\Models\Photo;

class Delete
{
	/**
	 * @throws ModelDBException
	 */
	public function do(array $photoIds): void
	{
		try {
			$photos = Photo::query()
				->with(['size_variants', 'size_variants.sym_links'])
				->whereIn('id', $photoIds)
				->get();

			/** @var Photo $photo */
			foreach ($photos as $photo) {
				// we must call delete on the model and not on the database
				// in order to remove the files, too
				$photo->delete();
			}
		} catch (\InvalidArgumentException $ignored) {
			// In theory whereIn may throw this exception,
			// but will never do so for array operands.
			return;
		}
	}
}
