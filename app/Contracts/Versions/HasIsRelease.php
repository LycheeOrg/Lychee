<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Versions;

/**
 * Must implement release getter.
 */
interface HasIsRelease
{
	/**
	 * Return true if current instance is a release.
	 *
	 * @return bool
	 */
	public function isRelease(): bool;
}