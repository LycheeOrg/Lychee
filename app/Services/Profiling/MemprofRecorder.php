<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Profiling;

use function Safe\fclose;
use function Safe\fopen;

/**
 * Thin wrapper around the `memprof` PECL extension's procedural API
 * (`memprof_enable()`, `memprof_disable()`, `memprof_dump_pprof()`).
 *
 * The extension is optional and not installed on most systems (it is not a
 * Composer dependency — see Feature 053's spec for the corrected reference).
 * Every method here is a no-op (or returns a safe default) when the
 * extension isn't loaded, so callers never need their own
 * `function_exists()` guard beyond {@see self::isAvailable()}.
 */
class MemprofRecorder
{
	public function isAvailable(): bool
	{
		return \function_exists('memprof_enable');
	}

	public function enable(): void
	{
		if ($this->isAvailable()) {
			\memprof_enable();
		}
	}

	public function disable(): void
	{
		if ($this->isAvailable()) {
			\memprof_disable();
		}
	}

	/**
	 * Dumps the current memory profile, in pprof format, to the given path.
	 *
	 * @param string $absolute_path destination file path
	 */
	public function dumpPprof(string $absolute_path): void
	{
		if (!$this->isAvailable()) {
			return;
		}

		$handle = fopen($absolute_path, 'wb');
		try {
			\memprof_dump_pprof($handle);
		} finally {
			fclose($handle);
		}
	}
}
