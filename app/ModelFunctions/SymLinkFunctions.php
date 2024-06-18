<?php

declare(strict_types=1);

namespace App\ModelFunctions;

use App\Exceptions\ModelDBException;
use App\Models\SymLink;

class SymLinkFunctions
{
	/**
	 * Clear the table of existing SymLinks.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function clearSymLink(): void
	{
		$symLinks = SymLink::all();
		/** @var SymLink $symLink */
		foreach ($symLinks as $symLink) {
			$symLink->delete();
		}
	}

	/**
	 * Remove outdated SymLinks.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 */
	public function remove_outdated(): void
	{
		$symLinks = SymLink::expired()->get();
		/** @var SymLink $symLink */
		foreach ($symLinks as $symLink) {
			$symLink->delete();
		}
	}
}
