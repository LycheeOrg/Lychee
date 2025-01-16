<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\InitDTO;

/**
 * Initial pipes, could be seen as pre-processing steps.
 */
interface InitPipe
{
	/**
	 * @param InitDTO                           $state
	 * @param \Closure(InitDTO $state): InitDTO $next
	 *
	 * @return InitDTO
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO;
}