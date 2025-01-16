<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\PhotoPartner;

use App\Contracts\PhotoCreate\PhotoPartnerPipe;
use App\DTO\PhotoCreate\PhotoPartnerDTO;

class DeleteOldVideoPartner implements PhotoPartnerPipe
{
	public function handle(PhotoPartnerDTO $state, \Closure $next): PhotoPartnerDTO
	{
		$state->oldVideo->delete();

		return $next($state);
	}
}
