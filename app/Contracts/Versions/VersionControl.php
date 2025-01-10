<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Versions;

/**
 * Interface to pack  all the required information with regard to the Git version of Lychee.
 */
interface VersionControl
{
	/**
	 * Hydrate the Remote Data.
	 *
	 * @param bool $withRemote
	 * @param bool $useCache
	 *
	 * @return void
	 */
	public function hydrate(bool $withRemote = true, bool $useCache = true): void;

	/**
	 * are we up to date.
	 *
	 * @return bool
	 */
	public function isUpToDate(): bool;
}