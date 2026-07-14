<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Search\Strategies\Traits;

/**
 * Escapes the LIKE wildcard characters `%` and `_` (and the escape
 * character itself) using `!` as the escape character.
 *
 * Callers must pair this with an explicit `ESCAPE '!'` clause on the
 * corresponding `LIKE` predicate, otherwise the escaping has no effect and
 * `%`/`_` in the search value can still behave as wildcards.
 */
trait EscapesLikeWildcards
{
	private function escapeLike(string $value): string
	{
		return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
	}
}
