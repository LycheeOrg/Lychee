<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

/**
 * Basic definition of a Photo creation pipe.
 */
interface PhotoPipe
{
	/**
	 * @param PhotoDTO                            $state
	 * @param \Closure(PhotoDTO $state): PhotoDTO $next
	 *
	 * @return PhotoDTO
	 */
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO;
}