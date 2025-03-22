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
		$sym_links = SymLink::all();
		/** @var SymLink $symLink */
		foreach ($sym_links as $sym_link) {
			$sym_link->delete();
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
		$sym_links = SymLink::expired()->get();
		/** @var SymLink $symLink */
		foreach ($sym_links as $sym_link) {
			$sym_link->delete();
		}
	}
}
