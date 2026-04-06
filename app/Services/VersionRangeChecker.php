<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\DTO\Version;
use Illuminate\Support\Facades\Log;
use function Safe\preg_match;

/**
 * Pure service that evaluates whether a Lychee version falls within a
 * comma-separated semver constraint range as returned by the GitHub
 * Security Advisories API (e.g. ">= 5.0.0, < 5.1.2").
 *
 * Supported operators: >=, <=, >, <, =, !=
 *
 * When a token has no operator (e.g., "7.1.0"), it is treated as ">= 7.1.0"
 * (all versions including and above that value are considered affected).
 *
 * A null or empty range string is treated as "matches all versions" (returns true).
 * Malformed tokens are skipped with a warning log entry.
 */
class VersionRangeChecker
{
	/**
	 * Determine whether the given version satisfies all constraints in a
	 * comma-separated range string.
	 *
	 * @param Version $version the installed Lychee version
	 * @param string  $range   comma-separated semver constraint string
	 *
	 * @return bool true when the version is within the range (i.e. vulnerable)
	 */
	public function matches(Version $version, string $range): bool
	{
		$range = trim($range);

		if ($range === '') {
			// An empty range means "affects all versions".
			return true;
		}

		$tokens = explode(',', $range);

		foreach ($tokens as $token) {
			$token = trim($token);

			if (!$this->evaluateToken($version, $token)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Evaluate a single constraint token such as ">= 5.0.0".
	 *
	 * When no operator is present (e.g., "7.1.0"), it is treated as ">= 7.1.0"
	 * (all versions including and above that value are considered affected).
	 *
	 * @param Version $version installed version
	 * @param string  $token   single trimmed constraint string
	 *
	 * @return bool result of the constraint evaluation; true when satisfied
	 */
	private function evaluateToken(Version $version, string $token): bool
	{
		// Parse operator prefix: >=, <=, !=, >, <, =
		if (preg_match('/^(>=|<=|!=|>|<|=)\s*(.+)$/', $token, $matches) !== 1) {
			// No operator found — treat as ">=" by default
			// (i.e., "7.1.0" means ">= 7.1.0")
			try {
				$constraint = Version::createFromString(trim($token));
			} catch (\Throwable) {
				Log::warning('SecurityAdvisories: unable to parse version "' . $token . '" — skipping.');

				return true;
			}

			$installed = $version->toInteger();
			$bound = $constraint->toInteger();

			return $installed >= $bound;
		}

		$operator = $matches[1];
		$constraint_str = trim($matches[2]);

		try {
			$constraint = Version::createFromString($constraint_str);
		} catch (\Throwable) {
			Log::warning('SecurityAdvisories: unable to parse version "' . $constraint_str . '" in token "' . $token . '" — skipping.');

			return true;
		}

		$installed = $version->toInteger();
		$bound = $constraint->toInteger();

		return match ($operator) {
			'>=' => $installed >= $bound,
			'<=' => $installed <= $bound,
			'!=' => $installed !== $bound,
			'>' => $installed > $bound,
			'<' => $installed < $bound,
			'=' => $installed === $bound,
			default => true, // @codeCoverageIgnore
		};
	}
}
