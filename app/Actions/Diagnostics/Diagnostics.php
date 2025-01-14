<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics;

class Diagnostics
{
	/**
	 * Constructs a formatted message line.
	 *
	 * Ensures that all messages lines are properly indented.
	 * A key must be at most 32 characters long.
	 * Shorter keys are padded with spaces to the right.
	 *
	 * @param string $key   the key of the message
	 * @param string $value the value of the message
	 *
	 * @return string a formatted message line
	 */
	public static function line(string $key, string $value): string
	{
		return sprintf('%-42s %s', $key, $value);
	}
}
