<?php

namespace App\Actions\Diagnostics;

trait Line
{
	/**
	 * Return the padded string to 32.
	 */
	public function line(string $key, string $value): string
	{
		return sprintf('%-32s %s', $key, $value);
	}
}
