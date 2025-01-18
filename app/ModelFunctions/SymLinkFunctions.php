<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
	 *
	 * @codeCoverageIgnore Only tested locally
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
	 *
	 * @codeCoverageIgnore Only tested locally
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
