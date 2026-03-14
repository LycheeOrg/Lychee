<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Search;

/**
 * Represents a single parsed search token from the query string.
 *
 * Examples:
 *   "sunset"             → modifier=null,     sub_modifier=null,  operator=null, value="sunset",      is_prefix=false
 *   "tag:sunset"         → modifier="tag",     sub_modifier=null,  operator=null, value="sunset",      is_prefix=false
 *   "tag:sun*"           → modifier="tag",     sub_modifier=null,  operator=null, value="sun",         is_prefix=true
 *   "date:>2024-01-01"   → modifier="date",    sub_modifier=null,  operator=">",  value="2024-01-01",  is_prefix=false
 *   "ratio:landscape"    → modifier="ratio",   sub_modifier=null,  operator=null, value="landscape",   is_prefix=false
 *   "color:#ff0000"      → modifier="color",   sub_modifier=null,  operator=null, value="#ff0000",     is_prefix=false
 *   "rating:avg:>=4"     → modifier="rating",  sub_modifier="avg", operator=">=", value="4",           is_prefix=false
 *   "rating:own:>=3"     → modifier="rating",  sub_modifier="own", operator=">=", value="3",           is_prefix=false
 */
readonly class SearchToken
{
	/**
	 * @param string|null $modifier     The modifier keyword (e.g. "tag", "date", "rating"), null for plain-text terms.
	 * @param string|null $sub_modifier the sub-modifier; currently only used for "rating" ("avg" or "own")
	 * @param string|null $operator     comparison operator: "<", "<=", ">", ">=", "=", or null for exact/LIKE
	 * @param string      $value        the search value (already stripped of operator and trailing "*")
	 * @param bool        $is_prefix    true when a trailing "*" was detected (prefix LIKE mode)
	 */
	public function __construct(
		public ?string $modifier,
		public ?string $sub_modifier,
		public ?string $operator,
		public string $value,
		public bool $is_prefix,
	) {
	}
}
