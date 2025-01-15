<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\PhotoPartnerDTO;

/**
 * Basic definition of a Photo Partner pipe.
 */
interface PhotoPartnerPipe
{
	/**
	 * @param PhotoPartnerDTO                                   $state
	 * @param \Closure(PhotoPartnerDTO $state): PhotoPartnerDTO $next
	 *
	 * @return PhotoPartnerDTO
	 */
	public function handle(PhotoPartnerDTO $state, \Closure $next): PhotoPartnerDTO;
}