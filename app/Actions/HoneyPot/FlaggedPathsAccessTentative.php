<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\HoneyPot;

use function Safe\preg_match;

/**
 * Anyone trying to access a path from the cross product in config.php is not with good intentions.
 */
class FlaggedPathsAccessTentative extends BasePipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(string $path, \Closure $next): never
	{
		/** @var array<int,string> $honeypot_paths_array */
		$honeypot_paths_array = config('honeypot.paths', []);

		/** @var array<int,array{prefix:array<int,string>,suffix:array<int,string>}> $honeypot_xpaths_array */
		$honeypot_xpaths_array = config('honeypot.xpaths', []);

		foreach ($honeypot_xpaths_array as $xpaths) {
			foreach ($xpaths['prefix'] as $prefix) {
				foreach ($xpaths['suffix'] as $suffix) {
					$honeypot_paths_array[] = $prefix . $suffix;
				}
			}
		}

		// Turn the path array into a regex pattern.
		// We escape . and / to avoid confusions with other regex characters
		$honeypot_paths = '/^(' . str_replace(['.', '/'], ['\.', '\/'], implode('|', $honeypot_paths_array)) . ')/i';

		// If the user tries to access a honeypot path, fail with the teapot code.
		if (preg_match($honeypot_paths, $path) !== 0) {
			$this->throwTeaPot($path);
		}

		$next($path);
	}
}
