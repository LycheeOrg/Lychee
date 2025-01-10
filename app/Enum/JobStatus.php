<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

/**
 * current JobStatus of Lychee.
 * 0 = ready
 * 1 = success
 * 2 = failure.
 */
enum JobStatus: int
{
	case READY = 0;
	case SUCCESS = 1;
	case FAILURE = 2;
	case STARTED = 3;

	/**
	 * Given a JobStatus return the associated name.
	 *
	 * @return string
	 */
	public function name(): string
	{
		return match ($this) {
			self::READY => 'ready',
			self::SUCCESS => 'success',
			self::FAILURE => 'failure',
			self::STARTED => 'started',
		};
	}
}
