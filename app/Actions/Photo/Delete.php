<?php

namespace App\Actions\Photo;

use App\Models\Photo;

class Delete
{
	public function do(array $photoIds): void
	{
		$photos = Photo::query()
			->with(['size_variants_raw', 'size_variants_raw.sym_links'])
			->whereIn('id', $photoIds)
			->get();
		$success = true;
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			// we must call delete on the model and not on the database
			// in order to remove the files, too
			$success &= $photo->delete();
		}
		abort_if(!$success, 500, 'could not delete photo(s)');
	}
}
