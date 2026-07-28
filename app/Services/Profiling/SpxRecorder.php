<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Profiling;

/**
 * Thin wrapper around the `spx` PECL extension's (NoiseByNorthwest/php-spx)
 * manual span-control API (`spx_profiler_start()`, `spx_profiler_stop()`).
 *
 * The extension is optional and not installed on most systems. Every method
 * here is a no-op (or returns a safe default) when the extension isn't
 * loaded, so callers only need {@see self::isAvailable()} as a guard.
 *
 * Manual start/stop (rather than SPX's own ini-only "always profiling" mode)
 * is deliberate: SPX's own documentation ("Handle long-living / daemon
 * processes") recommends exactly this pattern for persistent-worker runtimes
 * such as Laravel Octane/FrankenPHP, where a single PHP process serves many
 * logical requests and automatic per-request start/stop semantics are not
 * guaranteed. This requires `spx.http_profiling_auto_start=0` to be set
 * (see the how-to guide / Dockerfile startup script).
 */
class SpxRecorder
{
	public function isAvailable(): bool
	{
		return \function_exists('spx_profiler_start') && \function_exists('spx_profiler_stop');
	}

	public function start(): void
	{
		if ($this->isAvailable()) {
			\spx_profiler_start();
		}
	}

	/**
	 * @return string|null the SPX report key (used to build the analysis-screen URL), or null if unavailable/not captured
	 */
	public function stop(): ?string
	{
		if (!$this->isAvailable()) {
			return null;
		}

		/** @var string|null $key */
		$key = \spx_profiler_stop();

		return $key !== '' ? $key : null;
	}
}
