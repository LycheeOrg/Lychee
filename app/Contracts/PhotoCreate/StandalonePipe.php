<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Basic definition of a Standalone Photo pipe.
 */
interface StandalonePipe
{
	/**
	 * @param StandaloneDTO                                 $state
	 * @param \Closure(StandaloneDTO $state): StandaloneDTO $next
	 *
	 * @return StandaloneDTO
	 */
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO;
}