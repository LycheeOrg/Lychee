<?php

namespace App\Observers;

use App\Models\SizeVariant;
use Illuminate\Support\Facades\Storage;

class SizeVariantObserver
{
	/**
	 * Callback for the SizeVariant "deleting" event.
	 *
	 * This method deletes the actual file from storage, before a
	 * {@link \App\Models\SizeVariant} is deleted from the database.
	 *
	 * @param SizeVariant $sizeVariant the size variant to be deleted
	 *
	 * @return bool true, if the framework may continue with deletion, false otherwise
	 */
	public function deleting(SizeVariant $sizeVariant): bool
	{
		$disk = Storage::disk();
		$shortPath = $sizeVariant->short_path;
		if (!empty($shortPath) && $disk->exists($shortPath)) {
			return $disk->delete($shortPath);
		}

		return true;
	}
}
