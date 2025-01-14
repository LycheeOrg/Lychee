<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Basic definition of a Photo shared pipe.
 *
 * This pipes makes use of union types (|) to support the different DTO.
 */
interface SharedPipe
{
	/**
	 * @param StandaloneDTO|DuplicateDTO                                                $state
	 * @param \Closure(StandaloneDTO|DuplicateDTO $state): (StandaloneDTO|DuplicateDTO) $next
	 *
	 * @return StandaloneDTO|DuplicateDTO
	 */
	public function handle(StandaloneDTO|DuplicateDTO $state, \Closure $next): StandaloneDTO|DuplicateDTO;
}