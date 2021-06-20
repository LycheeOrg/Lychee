<?php

namespace App\Observers;

use App\Facades\Helpers;
use App\Models\SymLink;
use Illuminate\Support\Facades\Storage;

class SymLinkObserver
{
	/**
	 * Callback for the SymLink "creating" event.
	 *
	 * This method creates the actual symbolic link on disk, before a
	 * {@link \App\Models\SymLink} is inserted into the database.
	 * If this method cannot create the symbolic link, then this method
	 * cancels the insert operation.
	 *
	 * @param SymLink $symLink The symbolic link to be created
	 *
	 * @throws \Exception Thrown, if this method could not gather enough
	 *                    entropy for a salt
	 *
	 * @return bool True, if the framework may continue with creation, false otherwise
	 */
	public function creating(SymLink $symLink): bool
	{
		$origFullPath = $symLink->size_variant->full_path;
		$extension = Helpers::getExtension($origFullPath);
		$symShortPath = hash('sha256', random_bytes(32) . '|' . $origFullPath) . $extension;
		$symFullPath = Storage::disk(SymLink::DISK_NAME)->path($symShortPath);
		unlink($symFullPath);
		if (!symlink($origFullPath, $symFullPath)) {
			return false;
		}
		$symLink->short_path = $symShortPath;

		return true;
	}

	/**
	 * Callback for the SymLink "deleting" event.
	 *
	 * This method deletes the actual symbolic link from storage, before a
	 * {@link \App\Models\SymLink} is deleted from the database.
	 * If this method cannot delete the symbolic link, then this method
	 * cancels the delete operation.
	 *
	 * @param SymLink $symLink The symbolic link to be deleted
	 *
	 * @return bool True, if the framework may continue with deletion, false otherwise
	 */
	public function deleting(SymLink $symLink): bool
	{
		$disk = Storage::disk(SymLink::DISK_NAME);
		if (!empty($symLink->short_path) && $disk->exists($symLink->short_path)) {
			return $disk->delete($symLink->short_path);
		}

		return true;
	}
}
