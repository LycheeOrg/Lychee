<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Constants;

/**
 * Bounds on the number of parameters a single query binds.
 */
class QueryLimits
{
	/**
	 * Maximum number of ids passed to one whereIn() clause.
	 *
	 * Large id lists fail on MySQL above about 1000 bound parameters.
	 * At 500, a query that takes two id lists still binds at most 1000.
	 */
	public const ID_CHUNK_SIZE = 500;
}
