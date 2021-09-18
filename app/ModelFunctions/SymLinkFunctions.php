<?php

namespace App\ModelFunctions;

use App\Exceptions\ModelDBException;
use App\Models\SymLink;

class SymLinkFunctions
{
	/**
	 * Clear the table of existing SymLinks.
	 *
	 * @throws ModelDBException
	 */
	public function clearSymLink(): void
	{
		$symlinks = SymLink::all();
		$success = true;
		$lastException = null;
		foreach ($symlinks as $symlink) {
			try {
				$success &= $symlink->delete();
			} catch (\Throwable $e) {
				$lastException = $e;
			}
		}
		if (!$success || $lastException !== null) {
			throw ModelDBException::create('symbolic link', 'delete', $lastException);
		}
	}

	/**
	 * Remove outdated SymLinks.
	 *
	 * @return bool
	 */
	public function remove_outdated()
	{
		$symlinks = SymLink::expired()->get();
		$success = true;
		/** @var SymLink $symlink */
		foreach ($symlinks as $symlink) {
			$success &= $symlink->delete();
		}

		return $success;
	}
}
