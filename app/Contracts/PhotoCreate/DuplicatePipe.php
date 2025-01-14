<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\DuplicateDTO;

/**
 * Basic definition of a Duplicate Photo pipe.
 */
interface DuplicatePipe
{
	/**
	 * @param DuplicateDTO                                $state
	 * @param \Closure(DuplicateDTO $state): DuplicateDTO $next
	 *
	 * @return DuplicateDTO
	 */
	public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO;
}