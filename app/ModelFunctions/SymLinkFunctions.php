<?php

namespace App\ModelFunctions;

use App\Models\SymLink;

class SymLinkFunctions
{
	/**
	 * Clear the table of existing SymLinks.
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function clearSymLink(): string
	{
		$symlinks = SymLink::all();
		$no_error = true;
		foreach ($symlinks as $symlink) {
			$no_error &= $symlink->delete();
		}

		return $no_error ? 'true' : 'false';
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
