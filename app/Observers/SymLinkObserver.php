<?php

namespace App\Observers;

use App\Exceptions\InsufficientEntropyException;
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
	 * @return bool True, if the framework may continue with creation,
	 *              false otherwise
	 *
	 * @throws InsufficientEntropyException
	 */
	public function creating(SymLink $symLink): bool
	{
		$origFullPath = $symLink->size_variant->full_path;
		$extension = Helpers::getExtension($origFullPath);
		try {
			$symShortPath = hash('sha256', random_bytes(32) . '|' . $origFullPath) . $extension;
		} catch (\Exception $e) {
			throw new InsufficientEntropyException($e);
		}
		$symFullPath = Storage::disk(SymLink::DISK_NAME)->path($symShortPath);
		if (is_link($symFullPath)) {
			unlink($symFullPath);
		}
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
		// Laravel and Flysystem does not support symbolic links.
		// So we must use low-level methods here.
		$fullPath = $symLink->full_path;

		return (!is_link($fullPath) && !file_exists($fullPath)) || (is_link($fullPath) && unlink($fullPath));
	}
}
