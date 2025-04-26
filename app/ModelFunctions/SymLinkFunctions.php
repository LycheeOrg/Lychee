<?php

/**
 * SPDX-License-Identifier: MIT
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
	 * @throws ModelDBException
	 *
	 * @codeCoverageIgnore Only tested locally
	 */
	public function clearSymLink(): void
	{
		$sym_links = SymLink::all();
		foreach ($sym_links as $sym_link) {
			$sym_link->delete();
		}
	}

	/**
	 * Remove outdated SymLinks.
	 *
	 * @throws ModelDBException
	 *
	 * @codeCoverageIgnore Only tested locally
	 */
	public function remove_outdated(): void
	{
		$sym_links = SymLink::expired()->get();
		foreach ($sym_links as $sym_link) {
			$sym_link->delete();
		}
	}
}