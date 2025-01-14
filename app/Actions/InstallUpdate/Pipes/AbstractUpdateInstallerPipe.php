<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate\Pipes;

abstract class AbstractUpdateInstallerPipe
{
	/**
	 * @param array<int,string>                                      &$output
	 * @param \Closure(array<int,string> $output): array<int,string> $next
	 *
	 * @return array<int,string>
	 *
	 * @codeCoverageIgnore
	 */
	abstract public function handle(array &$output, \Closure $next): array;

	/**
	 * Arrayify a string and append it to $output.
	 *
	 * @param string   $string message text which each message separated by newline
	 * @param string[] $output list of messages
	 *
	 * @return void
	 */
	protected function strToArray(string $string, array &$output): void
	{
		$a = explode("\n", $string);
		foreach ($a as $aa) {
			if ($aa !== '') {
				$output[] = $aa;
			}
		}
	}
}