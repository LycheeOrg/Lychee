<?php

declare(strict_types=1);

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
		return sprintf('%-40s %s', $key, $value);
	}
}
