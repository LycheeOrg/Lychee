<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * An IP address blocked from logging in (Feature 112, DO-112-01).
 */
final readonly class BlockedIp
{
	/**
	 * @param string $ip            canonical IP address
	 * @param int    $attempts      login attempts counted in the window
	 * @param int    $blocked_until unix time at which the block ends
	 */
	public function __construct(
		public string $ip,
		public int $attempts,
		public int $blocked_until,
	) {
	}
}
