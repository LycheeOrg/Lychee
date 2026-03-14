<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\DTO\Search\SearchToken;

/**
 * Request contract for endpoints that carry parsed search tokens.
 */
interface HasSearchTokens
{
	/**
	 * @return SearchToken[]
	 */
	public function tokens(): array;
}
